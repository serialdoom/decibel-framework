<?php
namespace tests\app\decibel\archive;

use app\decibel\archive\DZipArchive;
use app\decibel\file\DFileExistsException;
use app\decibel\test\DTestCase;

/**
 * Test class for DZipArchive.
 * Generated by Decibel on 2011-10-31 at 14:13:49.
 */
class DZipArchiveTest extends DTestCase
{
    private $fixtureDir;

    public function setUp()
    {
        $this->fixtureDir = __DIR__ . '/../_fixtures/';
    }
    /**
     * @covers app\decibel\archive\DZipArchive::__construct
     */
    public function testCreateArchive()
    {
        $archive = new DZipArchive($this->fixtureDir . 'zip/container.zip', true);
        $this->assertInstanceOf(DZipArchive::class, $archive);
    }

    /**
     * @covers app\decibel\archive\DZipArchive::__construct
     */
    public function testCreateArchiveBadDestinationThrowsException()
    {
        $this->setExpectedException(DFileExistsException::class);
        new DZipArchive($this->fixtureDir . 'xml/manifest.xml', false);
    }

    /**
     * @covers app\decibel\archive\DZipArchive::addDirectory
     */
    public function testAddDirectoryToArchive()
    {
        $archive = new DZipArchive($this->fixtureDir . 'zip/container.zip', true);
        $archive->addDirectory($this->fixtureDir . 'xml/');
    }
}
