<?php
namespace tests\app\decibel\model\field;

use app\decibel\stream\DFileStream;
use app\decibel\stream\DStreamException;
use app\decibel\test\DTestCase;
use app\decibel\xml\DDocumentParseException;
use app\decibel\xml\DXPath;
use DOMDocument;

/**
 * Test class for DXPath.
 */
class DXPathTest extends DTestCase
{
    /** @var string */
    private $xmlFixtureDir;

    public function setUp()
    {
        $this->xmlFixtureDir = __DIR__ . '/../_fixtures/xml/';
    }

    /**
     * @covers app\decibel\xml\DXPath::create
     * @expectedException app\decibel\stream\DStreamReadException
     */
    public function test__constructBadFilename()
    {
        $stream = new DFileStream('invalid_filename');
        DXPath::create($stream);
    }

    /**
     * @covers app\decibel\xml\DXPath::create
     */
    public function testConstructWithMalformedXmlThrowsException()
    {
        $this->setExpectedException(DDocumentParseException::class);

        $stream = new DFileStream($this->xmlFixtureDir . 'malformed.xml');
        DXPath::create($stream);
    }

    /**
     * @covers app\decibel\xml\DXPath::create
     */
    public function testConstruct()
    {
        $stream = new DFileStream($this->xmlFixtureDir . 'manifest.xml');
        $this->assertInstanceOf(DXPath::class, DXPath::create($stream));
    }

    /**
     * @covers app\decibel\xml\DXPath::getDocument
     */
    public function testgetDocument()
    {
        $stream = new DFileStream($this->xmlFixtureDir . 'manifest.xml');
        $xpath = DXPath::create($stream);

        $this->assertInstanceOf(DXPath::class, $xpath);
        $this->assertInstanceOf(DOMDocument::class, $xpath->getDocument());
    }

    /**
     * @covers app\decibel\xml\DXPath::createFromHtml
     */
    public function testCreateFromHtml()
    {
        $stream = new DFileStream($this->xmlFixtureDir . 'manifest.xml');
        $this->assertInstanceOf(DXPath::class, DXPath::createFromHtml($stream));
    }

    /**
     * @covers app\decibel\xml\DXPath::createFromHtml
     */
    public function testCreateFromHtmlWithBadFilenameThrowsException()
    {
        $this->setExpectedException(DStreamException::class);

        $stream = new DFileStream('invalid');
        DXPath::createFromHtml($stream);
    }

    /**
     * @covers app\decibel\xml\DXPath::createFromHtml
     */
    public function testCreateFromHtmlWithMalformedXmlThrowsException()
    {
        $this->setExpectedException(DDocumentParseException::class);

        $stream = new DFileStream($this->xmlFixtureDir . 'malformed.xml');
        DXPath::create($stream);
    }
}
