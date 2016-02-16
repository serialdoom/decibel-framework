<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DDistinguishedName;
use app\decibel\ssl\DPrivateKey;
use app\decibel\ssl\DSigningRequest;
use app\decibel\stream\DFileStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DSigningRequest.
 */
class DSigningRequestTest extends DTestCase
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
     * @var DSigningRequest
     */
    protected static $signingRequest;
    /**
     * @var DDistinguishedName
     */
    protected static $dn;

    /**
     * @covers app\decibel\ssl\DSigningRequest::__construct
     * @covers app\decibel\ssl\DSigningRequest::generate
     */
    public function testgenerate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertInstanceOf(DSigningRequest::class, self::$signingRequest);
    }

    /**
     * @covers app\decibel\ssl\DSigningRequest::generateDebug
     */
    public function testgenerateDebug()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $debug = self::$signingRequest->generateDebug();
        $this->assertSame(1, count($debug));
        $this->assertArrayHasKey('signingRequest', $debug);
        $csr = trim($debug['signingRequest']);
        $this->assertStringStartsWith("-----BEGIN CERTIFICATE REQUEST-----\n", $csr);
        $this->assertStringEndsWith("\n-----END CERTIFICATE REQUEST-----", $csr);
    }

    /**
     * @covers app\decibel\ssl\DSigningRequest::getPublicKey
     */
    public function testgetPublicKey()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $publicKey = self::$signingRequest->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
    }

    /**
     * @covers app\decibel\ssl\DSigningRequest::getSubject
     */
    public function testgetSubject()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertSame(
            self::$dn->getSubject(),
            self::$signingRequest->getSubject()
        );
    }

    /**
     * @covers app\decibel\ssl\DSigningRequest::export
     */
    public function testexport()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $csr = trim(self::$signingRequest->export());
        $this->assertStringStartsWith("-----BEGIN CERTIFICATE REQUEST-----\n", $csr);
        $this->assertStringEndsWith("\n-----END CERTIFICATE REQUEST-----", $csr);
    }

    /**
     * @covers app\decibel\ssl\DSigningRequest::save
     */
    public function testsave()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $tempFilename = tempnam(TEMP_PATH, 'csr');
        $stream = new DFileStream($tempFilename);
        self::$signingRequest->save($stream);
        $this->assertFileExists($tempFilename);
        $this->assertSame(file_get_contents($tempFilename), self::$signingRequest->export());
        @unlink($tempFilename);
    }
}
