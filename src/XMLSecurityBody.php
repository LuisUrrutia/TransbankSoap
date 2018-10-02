<?php

namespace LuisUrrutia\TransbankSoapValidation;

class XMLSecurityBody
{

    public $bodyNode = null;

    private function canonicalizeBody($canonicalMethod)
    {
        if ($this->bodyNode instanceof \DOMNode) {
            $exclusive = false;
            $withComments = false;
            switch ($canonicalMethod) {
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                    $exclusive = false;
                    $withComments = false;
                    break;
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                    $withComments = true;
                    break;
                case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                    $exclusive = true;
                    break;
                case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                    $exclusive = true;
                    $withComments = true;
                    break;
            }

            return $this->bodyNode->C14N($exclusive, $withComments);
        }
        return null;
    }

    public function locateBody($objDoc, $pos = 0)
    {
        if ($objDoc instanceof \DOMDocument) {
            $doc = $objDoc;
        } else {
            $doc = $objDoc->ownerDocument;
        }
        if ($doc) {
            $ns = $doc->documentElement->namespaceURI;

            $xpath = new \DOMXPath($doc);
            $xpath->registerNamespace('soap', $ns);
            $query = ".//soap:Body";
            $nodeSet = $xpath->query($query, $objDoc);
            $this->bodyNode = $nodeSet->item($pos);
            return $this->bodyNode;
        }
        return null;
    }

    public function compareDigest($digest, $canonicalMethod)
    {
        $body = $this->canonicalizeBody($canonicalMethod);
        $bodyDigest = base64_encode(sha1($body, true));
        return ($digest == $bodyDigest);
    }
}
