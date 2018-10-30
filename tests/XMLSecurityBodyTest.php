<?php
declare(strict_types=1);

namespace LuisUrrutia\TransbankSoapTests;

use PHPUnit\Framework\TestCase;
use LuisUrrutia\TransbankSoap\XMLSecurityBody;

class XMLSecurityBodyTest extends TestCase
{

    public function provider()
    {
        return [
            'Webpay Plus' => [
                $this->getSecurityBodyInstance(dirname(__FILE__) . '/fixtures/signedWebpayPlus.xml'),
                file_get_contents(dirname(__FILE__) . '/fixtures/canonicalizedWebpayPlusExpected.xml'),
                'TR_NORMAL_WShttp://test.dev/responsehttp://test.dev/thanks10000597020000541Orden824201',
                '42wTy5Me1YzNoWz9GXP0RYgdefY=',
            ],
        ];
    }


    public function getSecurityBodyInstance($path)
    {
        $xml = file_get_contents($path);

        $doc = new DOMDocument('1.0');
        $doc->loadXML($xml);

        $securityBody = new XMLSecurityBody();
        $securityBody->locateBody($doc);

        return $securityBody;
    }

    /**
     * @dataProvider provider
     */
    public function testLocateBody()
    {
        $args = func_get_args();
        $this->assertGreaterThanOrEqual(
            3,
            count($args)
        );

        $securityBody = $args[0];
        $bodyText = $args[2];

        $this->assertInstanceOf(
            XMLSecurityBody::class,
            $securityBody
        );

        $body = $securityBody->bodyNode;
        $this->assertInstanceOf(
            DOMNode::class,
            $body
        );

        $this->assertSame(
            $bodyText,
            $body->nodeValue
        );
    }

    /**
     * @dataProvider provider
     */
    public function testCanonicalizeBody()
    {
        $args = func_get_args();
        $this->assertGreaterThanOrEqual(
            2,
            count($args)
        );

        $securityBody = $args[0];
        $expected = $args[1];

        $this->assertInstanceOf(
            XMLSecurityBody::class,
            $securityBody
        );

        $reflection = new \ReflectionClass(get_class($securityBody));
        $method = $reflection->getMethod('canonicalizeBody');
        $method->setAccessible(true);

        $canonicalized = $method->invoke($securityBody);

        $this->assertSame(
            $expected,
            $canonicalized
        );
    }

    /**
     * @dataProvider provider
     */
    public function testDigest()
    {
        $args = func_get_args();
        $this->assertGreaterThanOrEqual(
            4,
            count($args)
        );

        $securityBody = $args[0];
        $digest = $args[3];

        $this->assertTrue(
            $securityBody->compareDigest($digest)
        );
    }
}
