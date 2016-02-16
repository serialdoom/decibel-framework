<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DCertificateGetter;
use app\decibel\test\DTestCase;

/**
 * Test class for DCertificateGetter.
 */
class DCertificateGetterTest extends DTestCase
{
    /**
     *
     */
    protected function setUp()
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped(
                'The OpenSSL extension is not available.'
            );
        }
    }

    /**
     * @covers app\decibel\ssl\DCertificateGetter::getCertificate
     */
    public function testgetCertificate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertInstanceOf(
            'app\\decibel\\ssl\\DCertificate',
            DCertificateGetter::getCertificate('www.decibeltechnology.com')
        );
    }

    /**
     * @covers app\decibel\ssl\DCertificateGetter::getCertificate
     * @expectedException app\decibel\rpc\debug\DInvalidRemoteProcedureCallException
     */
    public function testgetCertificate_InvalidRemoteProcedureException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        DCertificateGetter::getCertificate('invalid.decibeltechnology.com');
    }
}
