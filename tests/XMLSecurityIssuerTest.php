<?php
declare(strict_types=1);

namespace LuisUrrutia\TransbankSoapTests;

use PHPUnit\Framework\TestCase;
use LuisUrrutia\TransbankSoap\XMLSecurityIssuer;

class XMLSecurityIssuerTest extends TestCase
{
    public function provider()
    {
        return [
            'Webpay Plus' => [
                file_get_contents(dirname(__FILE__) . '/fixtures/signedWebpayPlus.xml'),
                file_get_contents(dirname(__FILE__) . '/certs/597020000541.crt'),
                file_get_contents(dirname(__FILE__) . '/fixtures/issuerWebpayPlusExpected.xml')
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testAddIssuer($xml, $cert, $expected)
    {
        $doc = new DOMDocument('1.0');
        $doc->loadXML($xml);

        $issuer = new XMLSecurityIssuer($doc);
        $issuer->addIssuer($cert);

        $xml = $doc->saveXML();

        $this->assertSame(
            trim($expected),
            trim($xml)
        );
    }
}
