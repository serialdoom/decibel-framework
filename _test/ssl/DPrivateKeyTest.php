<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DPrivateKey;
use app\decibel\stream\DFileStream;
use app\decibel\stream\DTextStream;
use app\decibel\test\DTestCase;

class TestPrivateKey extends DPrivateKey
{
    public static function test__construct_invalid()
    {
        new DPrivateKey('invalid');
    }
}

/**
 * Test class for DPrivateKey.
 */
class DPrivateKeyTest extends DTestCase
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
     * @covers app\decibel\ssl\DPrivateKey::__construct
     * @covers app\decibel\ssl\DPrivateKey::generate
     * @covers app\decibel\ssl\DPrivateKey::getBits
     */
    public function testgenerate_default()
    {
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $this->assertSame(2048, $privateKey->getBits());
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::__construct
     * @covers app\decibel\ssl\DPrivateKey::generate
     * @covers app\decibel\ssl\DPrivateKey::getBits
     */
    public function testgenerate_bits()
    {
        $privateKey = DPrivateKey::generate(1024);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $this->assertSame(1024, $privateKey->getBits());
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::__construct
     * @covers app\decibel\ssl\DPrivateKey::generate
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testgenerate_exception()
    {
        DPrivateKey::generate(10);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::__construct
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function test__construct_invalid()
    {
        TestPrivateKey::test__construct_invalid();
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::__destruct
     */
    public function test__destruct()
    {
        $privateKey = DPrivateKey::generate();
        unset($privateKey);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::generateDebug
     */
    public function testgenerateDebug()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $debug = $privateKey->generateDebug();
        $this->assertInternalType('array', $debug);
        $this->assertArrayHasKey('privateKey', $debug);
        $pem = trim($debug['privateKey']);
        $this->assertStringStartsWith("-----BEGIN RSA PRIVATE KEY-----\n", $pem);
        $this->assertStringEndsWith("\n-----END RSA PRIVATE KEY-----", $pem);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::export
     * @covers app\decibel\ssl\DPrivateKey::parse
     */
    public function testexport()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $pem = trim($privateKey->export());
        $this->assertStringStartsWith("-----BEGIN RSA PRIVATE KEY-----\n", $pem);
        $this->assertStringEndsWith("\n-----END RSA PRIVATE KEY-----", $pem);
        $privateKey2 = DPrivateKey::parse($pem);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey2);
        $this->assertSame($privateKey->export(), $privateKey2->export());
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::export
     * @covers app\decibel\ssl\DPrivateKey::parse
     */
    public function testexport_passphrase()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $passphrase = 'secret';
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $pem = trim($privateKey->export($passphrase));
        $this->assertStringStartsWith("-----BEGIN RSA PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: ", $pem);
        $this->assertStringEndsWith("\n-----END RSA PRIVATE KEY-----", $pem);
        $privateKey2 = DPrivateKey::parse($pem, $passphrase);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey2);
        $this->assertSame($privateKey->export(), $privateKey2->export());
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::parse
     * @expectedException app\decibel\ssl\DPrivateKeyParseException
     */
    public function testparse_exception()
    {
        DPrivateKey::parse('not a real pem');
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::save
     * @covers app\decibel\ssl\DPrivateKey::open
     */
    public function testsave()
    {
        $tempFilename = tempnam(TEMP_PATH, 'key');
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $writeStream = new DFileStream($tempFilename);
        $privateKey->save($writeStream);
        $this->assertFileExists($tempFilename);
        $this->assertSame(file_get_contents($tempFilename), $privateKey->export());
        $readStream = new DFileStream($tempFilename);
        $privateKey2 = DPrivateKey::open($readStream);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey2);
        $this->assertSame($privateKey->export(), $privateKey2->export());
        @unlink($tempFilename);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::save
     * @covers app\decibel\ssl\DPrivateKey::open
     */
    public function testsave_passphrase()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $passphrase = 'secret';
        $tempFilename = tempnam(TEMP_PATH, 'key');
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf(DPrivateKey::class, $privateKey);
        $writeStream = new DFileStream($tempFilename);
        $privateKey->save($writeStream, $passphrase);
        $this->assertFileExists($tempFilename);
        $pem = trim(file_get_contents($tempFilename));
        $this->assertStringStartsWith("-----BEGIN RSA PRIVATE KEY-----\nProc-Type: 4,ENCRYPTED\nDEK-Info: ", $pem);
        $this->assertStringEndsWith("\n-----END RSA PRIVATE KEY-----", $pem);
        $readStream = new DFileStream($tempFilename);
        $privateKey2 = DPrivateKey::open($readStream, $passphrase);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey2);
        $this->assertSame($privateKey->export(), $privateKey2->export());
        @unlink($tempFilename);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::getPublicKey
     * @covers app\decibel\ssl\DPrivateKey::sign
     */
    public function testsign()
    {
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $publicKey = $privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $signature = $privateKey->sign($data);
        $this->assertTrue($publicKey->verify($data, $signature));
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::getPublicKey
     * @covers app\decibel\ssl\DPrivateKey::decrypt
     */
    public function testdecrypt()
    {
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $publicKey = $privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $encrypted = $publicKey->encrypt($data);
        $this->assertSame($privateKey->decrypt($encrypted), $data);
    }

    /**
     * @covers app\decibel\ssl\DPrivateKey::getPublicKey
     * @covers app\decibel\ssl\DPrivateKey::encrypt
     */
    public function testencrypt()
    {
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $publicKey = $privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $encrypted = $privateKey->encrypt($data);
        $this->assertSame($publicKey->decrypt($encrypted), $data);
    }
}
