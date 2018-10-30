<?php
namespace LuisUrrutia\TransbankSoap;

use DOMDocument;
use RobRichards\WsePhp\WSSESoap;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class Process
{
    private $doc = null;

    public function __construct($request)
    {
        $doc = new DOMDocument('1.0');
        $doc->loadXML($request);

        $this->doc = $doc;
    }

    public function sign($key)
    {
        if (is_file($key)) {
            $key = file_get_contents($key);
        }

        $XMLSecurityKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        $XMLSecurityKey->loadKey($key, false, false);

        $WSSE = new WSSESoap($this->doc);
        $WSSE->signSoapDoc($XMLSecurityKey, array("insertBefore" => true));
    }

    public function addIssuer($cert)
    {
        if (is_file($cert)) {
            $cert = file_get_contents($cert);
        }

        $issuer = new XMLSecurityIssuer($this->doc);
        $issuer->addIssuer($cert);
    }

    public function getXML()
    {
        return $this->doc->saveXML();
    }
}
