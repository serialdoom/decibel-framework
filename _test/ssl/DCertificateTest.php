<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DCertificate;
use app\decibel\ssl\DDistinguishedName;
use app\decibel\ssl\DPrivateKey;
use app\decibel\ssl\DSigningRequest;
use app\decibel\stream\DFileStream;
use app\decibel\test\DTestCase;

class TestCertificate extends DCertificate
{
    public static function test__construct_invalid()
    {
        new DCertificate('invalid');
    }

    public function &testgetResource()
    {
        return $this->resource;
    }
}

/**
 * Test class for DCertificate.
 */
class DCertificateTest extends DTestCase
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
        $this->sslFixtureDir = __DIR__ . '/../_fixtures/ssl/';
    }

    /** @var string */
    private $sslFixtureDir;

    /**
     * @var DSigningRequest
     */
    protected static $signingRequest;
    /**
     * @var DPrivateKey
     */
    protected static $privateKey;
    /**
     * @var DDistinguishedName
     */
    protected static $dn;

    /**
     * @covers app\decibel\ssl\DCertificate::__construct
     * @covers app\decibel\ssl\DCertificate::generate
     * @covers app\decibel\ssl\DCertificate::export
     * @covers app\decibel\ssl\DCertificate::getValidity
     * @covers app\decibel\ssl\DCertificate::getSubject
     * @covers app\decibel\ssl\DCertificate::getIssuer
     * @covers app\decibel\ssl\DCertificate::isSelfSigned
     * @covers app\decibel\ssl\DCertificate::__destruct
     */
    public function testgenerate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $certificate = TestCertificate::generate(self::$signingRequest, self::$privateKey, 100);
        $this->assertInstanceOf('app\\decibel\\ssl\\DCertificate', $certificate);
        // Local timezone issues in openssl make exact testing impractical
        // so make sure it is within 12 hours either side.
        $validity = time() + (100 * 86400);
        $this->assertLessThanOrEqual($validity + 43200, $certificate->getValidity());
        $this->assertGreaterThanOrEqual($validity - 43200, $certificate->getValidity());
        $this->assertSame(self::$dn->getSubject(), $certificate->getSubject()->getSubject());
        $this->assertSame(self::$dn->getSubject(), $certificate->getIssuer()->getSubject());
        $this->assertTrue($certificate->isSelfSigned());
        $cert = $certificate->export();
        $this->assertStringStartsWith("-----BEGIN CERTIFICATE-----\n", $cert);
        $this->assertStringEndsWith("\n-----END CERTIFICATE-----\n", $cert);
        //		$resource =& $certificate->testgetResource();
        //		$this->assertInternalType('resource', $resource);
        //		unset($certificate);
        //		debug($resource);
        //		$this->assertNull($resource);
    }

    /**
     * @covers app\decibel\ssl\DCertificate::parse
     */
    public function testparse()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $pem = file_get_contents($this->sslFixtureDir . 'www.test.com.crt');
        $cert = DCertificate::parse($pem);
        $this->assertInstanceOf('app\\decibel\\ssl\\DCertificate', $cert);
    }

    /**
     * @covers app\decibel\ssl\DCertificate::parse
     * @expectedException app\decibel\ssl\DCertificateParseException
     */
    public function testparse_invalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        DCertificate::parse('invalid');
    }

    /**
     * @covers app\decibel\ssl\DCertificate::save
     * @covers app\decibel\ssl\DCertificate::open
     */
    public function testsave()
    {
        // XXX: FIXME
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $tempFilename = tempnam(TEMP_PATH, 'key');
        $certificate = DCertificate::generate(self::$signingRequest, self::$privateKey, 100);
        $this->assertInstanceOf('app\\decibel\\ssl\\DCertificate', $certificate);
        $writeStream = new DFileStream($tempFilename);
        $certificate->save($writeStream);
        $this->assertFileExists($tempFilename);
        $this->assertSame(file_get_contents($tempFilename), $certificate->export());
        $readStream = new DFileStream($tempFilename);
        $certificate2 = DCertificate::open($readStream);
        $this->assertInstanceOf('app\\decibel\\ssl\\DCertificate', $certificate2);
        $this->assertSame($certificate->export(), $certificate2->export());
        @unlink($tempFilename);
    }
}
