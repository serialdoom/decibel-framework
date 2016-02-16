<?php
namespace tests\app\decibel\model\field;

use app\decibel\stream\DFileStream;
use app\decibel\stream\DStreamReadException;
use app\decibel\test\DTestCase;
use app\decibel\xml\DDocumentParseException;
use app\decibel\xml\DDOMDocument;

/**
 * Test class for DDOMDocument.
 */
class DDOMDocumentTest extends DTestCase
{
    /** @var string */
    private $xmlFixtureDir;

    public function setUp()
    {
        $this->xmlFixtureDir = __DIR__ . '/../_fixtures/xml/';
    }

    /**
     * @covers app\decibel\xml\DDOMDocument::create
     */
    public function testFileStreamWithBadFilenameThrowsException()
    {
        $this->setExpectedException(DStreamReadException::class);

        $stream = new DFileStream('invalid_filename');
        DDOMDocument::create($stream);
    }

    /**
     * @covers app\decibel\xml\DDOMDocument::create
     */
    public function testMalformedXmlThrowsException()
    {
        $this->setExpectedException(DDocumentParseException::class);

        $stream = new DFileStream($this->xmlFixtureDir . 'malformed.xml');
        DDOMDocument::create($stream);
    }

    /**
     * @covers app\decibel\xml\DDOMDocument::create
     */
    public function testCreateWithValidXml()
    {
        $stream = new DFileStream($this->xmlFixtureDir . 'manifest.xml');
        $this->assertInstanceOf('\DOMDocument', DDOMDocument::create($stream));
    }

    /**
     * @covers app\decibel\xml\DDOMDocument::createFromHtml
     */
    public function testCreateFromHtml()
    {
        $stream = new DFileStream($this->xmlFixtureDir . 'manifest.xml');
        $this->assertInstanceOf('\DOMDocument', DDOMDocument::createFromHtml($stream));
    }

    /**
     * @covers app\decibel\xml\DDOMDocument::createFromHtml
     */
    public function testCreateFromHtmlWithBadFilenameThrowsException()
    {
        $this->setExpectedException(DStreamReadException::class);

        $stream = new DFileStream('invalid');
        DDOMDocument::createFromHtml($stream);
    }
}
