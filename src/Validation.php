<?php

namespace LuisUrrutia\TransbankSoap;

use DOMXPath;
use Exception;
use DOMDocument;
use \RobRichards\XMLSecLibs\XMLSecurityDSig;

class Validation
{
    const NS_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const NS_ENVELOPE = 'xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"';
    const NS_XMLDSIG = 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#"';

    private $doc = null;
    private $cert = null;
    private $digest = null;

    public function __construct($xml, $cert)
    {
        $doc = new DOMDocument("1.0");
        $doc->loadXML($xml);
        $this->doc = $doc;

        if (is_file($cert)) {
            $this->cert = file_get_contents($cert);
        } else {
            $this->cert = $cert;
        }
    }

    private function getSignatureValue($node)
    {
        $doc = $node->ownerDocument;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('secdsig', XMLSecurityDSig::XMLDSIGNS);
        $query = "string(./secdsig:SignatureValue)";
        $sigValue = $xpath->evaluate($query, $node);

        if (empty($sigValue)) {
            throw new Exception("Unable to locate SignatureValue");
        }

        return $sigValue;
    }

    private function addEnvelopeNamespace($xml)
    {
        return str_replace(self::NS_XMLDSIG, self::NS_XMLDSIG . " " . self::NS_ENVELOPE, $xml);
    }

    private function validateSignature()
    {
        $XMLSecurityDSig = new XMLSecurityDSig();
        $XMLSecurityDSig->idKeys = array('wsu:Id');
        $XMLSecurityDSig->idNS = array('wsu'=> self::NS_WSU);

        $node = $XMLSecurityDSig->locateSignature($this->doc);
        $signedInfo = $this->addEnvelopeNamespace($XMLSecurityDSig->canonicalizeSignedInfo());
        $XMLSecurityDSig->validateReference();

        $this->digest = $node->firstChild->nodeValue;

        $key = $XMLSecurityDSig->locateKey();
        $key->loadKey($this->cert, false, true);

        $signatureValue = $this->getSignatureValue($node);

        $valid = $key->verifySignature($signedInfo, base64_decode($signatureValue));

        if (!$valid) {
            throw new Exception('Invalid Signature');
        }

        return $key;
    }

    private function validateBody($key)
    {
        $XMLSecurityBody = new XMLSecurityBody();
        $XMLSecurityBody->locateBody($this->doc);
        $valid = $XMLSecurityBody->compareDigest($this->digest, $key->type);

        if (!$valid) {
            throw new Exception('Invalid Body');
        }
    }

    public function isValid()
    {
        $key = $this->validateSignature();
        $this->validateBody($key);

        return true;
    }
}
