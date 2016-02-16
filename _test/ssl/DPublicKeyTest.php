<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DPrivateKey;
use app\decibel\ssl\DPublicKey;
use app\decibel\test\DTestCase;

/**
 * Test class for DPublicKey.
 */
class DPublicKeyTest extends DTestCase
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
        self::$privateKey = DPrivateKey::generate();
    }

    /**
     * @var DPrivateKey
     */
    protected static $privateKey;

    /**
     * @covers app\decibel\ssl\DPublicKey::__construct
     */
    public function test__construct()
    {
        $privateKey = DPrivateKey::generate();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPrivateKey', $privateKey);
        $publicKey = $privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::__destruct
     */
    public function test__destruct()
    {
        $privateKey = DPrivateKey::generate();
        $publicKey = $privateKey->getPublicKey();
        unset($publicKey);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::__construct
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function test__constructException()
    {
        new DPublicKey(true);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::generateDebug
     */
    public function testgenerateDebug()
    {
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $debug = $publicKey->generateDebug();
        $this->assertInternalType('array', $debug);
        $this->assertArrayHasKey('publicKey', $debug);
        $pem = trim($debug['publicKey']);
        $this->assertStringStartsWith("-----BEGIN PUBLIC KEY-----\n", $pem);
        $this->assertStringEndsWith("\n-----END PUBLIC KEY-----", $pem);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::export
     * @covers app\decibel\ssl\DPublicKey::parse
     */
    public function testexport()
    {
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $pem = trim($publicKey->export());
        $this->assertStringStartsWith("-----BEGIN PUBLIC KEY-----\n", $pem);
        $this->assertStringEndsWith("\n-----END PUBLIC KEY-----", $pem);
        $publicKey2 = DPublicKey::parse($pem);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey2);
        $this->assertSame($publicKey->export(), $publicKey2->export());
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::parse
     * @expectedException app\decibel\ssl\DPublicKeyParseException
     */
    public function testparse_exception()
    {
        DPublicKey::parse('not a real pem');
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::save
     * @covers app\decibel\ssl\DPublicKey::open
     */
    public function testsave()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        
        $tempFilename = tempnam(TEMP_PATH, 'key');
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $publicKey->save($tempFilename);
        $this->assertFileExists($tempFilename);
        $this->assertSame(file_get_contents($tempFilename), $publicKey->export());
        $publicKey2 = DPublicKey::open($tempFilename);
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey2);
        $this->assertSame($publicKey->export(), $publicKey2->export());
        @unlink($tempFilename);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::open
     * @expectedException app\decibel\file\DFileNotFoundException
     */
    public function testopen_exception()
    {
        DPublicKey::open('not a real filename');
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::verify
     */
    public function testverify()
    {
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $signature = self::$privateKey->sign($data);
        $this->assertTrue($publicKey->verify($data, $signature));
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::decrypt
     */
    public function testdecrypt()
    {
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $encrypted = self::$privateKey->encrypt($data);
        $this->assertSame($publicKey->decrypt($encrypted), $data);
    }

    /**
     * @covers app\decibel\ssl\DPublicKey::encrypt
     */
    public function testencrypt()
    {
        $publicKey = self::$privateKey->getPublicKey();
        $this->assertInstanceOf('app\\decibel\\ssl\\DPublicKey', $publicKey);
        $data = 'SOME DATA';
        $encrypted = $publicKey->encrypt($data);
        $this->assertSame(self::$privateKey->decrypt($encrypted), $data);
    }
}
