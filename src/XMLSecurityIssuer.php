<?php

namespace LuisUrrutia\TransbankSoap;

use RobRichards\WsePhp\WSSESoap;
use RobRichards\XMLSecLibs\XMLSecurityDSig;

class XMLSecurityIssuer
{
    private $doc = null;

    public function __construct($doc)
    {
        $this->doc = $doc;
    }

    public function addIssuer($cert)
    {
        $certArray = openssl_x509_parse($cert);
        $name = trim(str_replace("/", ",", $certArray['name']), ',');
        $serialNumber = $certArray['serialNumber'];

        $XMLSecurityDSig = new XMLSecurityDSig();
        $node = $XMLSecurityDSig->locateSignature($this->doc);

        $keyInfo = $XMLSecurityDSig->createNewSignNode('KeyInfo');
        $tokenRef = $this->doc->createElementNS(WSSESoap::WSSENS, WSSESoap::WSSEPFX . ':SecurityTokenReference');
        $x509Data = $XMLSecurityDSig->createNewSignNode("X509Data");
        $x509IssuerSerial = $XMLSecurityDSig->createNewSignNode("X509IssuerSerial");
        $x509IssuerName = $XMLSecurityDSig->createNewSignNode("X509IssuerName", $name);

        $node->appendChild($keyInfo);
        $keyInfo->appendChild($tokenRef);
        $x509Data->appendChild($x509IssuerSerial);

        $x509SerialNumber = $XMLSecurityDSig->createNewSignNode("X509SerialNumber", $serialNumber);
        $x509IssuerSerial->appendChild($x509IssuerName);
        $x509IssuerSerial->appendChild($x509SerialNumber);
        $tokenRef->appendChild($x509Data);
    }
}
