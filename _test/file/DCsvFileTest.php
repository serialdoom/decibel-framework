<?php
namespace tests\app\decibel\file;

use app\decibel\file\DCsvFile;
use app\decibel\stream\DFileStream;
use app\decibel\stream\DTextStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DCsvFile.
 * Generated by Decibel on 2011-10-31 at 14:09:44.
 */
class DCsvFileTest extends DTestCase
{
    /** @var string */
    private $csvFixtureDir;

    public function setUp()
    {
        $this->csvFixtureDir = __DIR__ . '/../_fixtures/csv/';
    }

    /**
     * @covers app\decibel\file\DCsvFile::getExportFormatName
     */
    public function testGetExportFormatName()
    {
        $format = new DCsvFile();
        $this->assertSame('CSV', $format->getExportFormatName());
    }

    /**
     * @covers app\decibel\file\DCsvFile::startExport
     * @covers app\decibel\file\DCsvFile::exportRow
     * @covers app\decibel\file\DCsvFile::endExport
     */
    public function test_export()
    {
        $format = new DCsvFile();
        $stream = new DTextStream();
        $format->startExport($stream, array('Column 1', 'Column 2', 'Column 3'));
        $format->exportRow(array('A', 'B', 'C'));
        $format->endExport();
        $a = iconv("UTF-8", "UTF-16LE//IGNORE", 'A');
        $b = iconv("UTF-8", "UTF-16LE//IGNORE", 'B');
        $c = iconv("UTF-8", "UTF-16LE//IGNORE", 'C');
        $this->assertSame(
            "\"Column 1\",\"Column 2\",\"Column 3\"\n\"{$a}\",\"{$b}\",\"{$c}\"\n",
            $stream->read()
        );
        $this->assertTrue($stream->isOpen());
    }

    /**
     * @covers app\decibel\file\DCsvFile::parse
     */
    public function testparse()
    {
        $stream = new DFileStream($this->csvFixtureDir . 'test.csv');
        $this->assertSame(
            array(
                DCsvFile::KEY_HEADER => array('1A', '1B', '1C', '1D'),
                0                    => array(
                    '1A' => '2A',
                    '1B' => '2B',
                    '1C' => '2C',
                    '1D' => '2D',
                ),
                1                    => array(
                    '1A' => '3A',
                    '1B' => '3B',
                    '1C' => '3C',
                    '1D' => '3D',
                ),
                2                    => array(
                    '1A' => '4A',
                    '1B' => '4B',
                    '1C' => '4C',
                    '1D' => '4D',
                ),
                3                    => array(
                    '1A' => '5A',
                    '1B' => '5B',
                    '1C' => '5C',
                    '1D' => '5D',
                ),
            ),
            DCsvFile::parse($stream)
        );
    }

    /**
     * @covers app\decibel\file\DCsvFile::parse
     */
    public function testparse_noHeader()
    {
        $stream = new DFileStream($this->csvFixtureDir . 'test.csv');
        $this->assertSame(
            array(
                0 => array('1A', '1B', '1C', '1D'),
                1 => array('2A', '2B', '2C', '2D'),
                2 => array('3A', '3B', '3C', '3D'),
                3 => array('4A', '4B', '4C', '4D'),
                4 => array('5A', '5B', '5C', '5D'),
            ),
            DCsvFile::parse($stream, false)
        );
    }

    /**
     * Test deprecated passing of filename instead of stream.
     *
     * @covers app\decibel\file\DCsvFile::parse
     */
    public function testparse_filename()
    {
        $stream = new DFileStream($this->csvFixtureDir . 'test.csv');
        $this->assertSame(
            array(
                DCsvFile::KEY_HEADER => array('1A', '1B', '1C', '1D'),
                0                    => array(
                    '1A' => '2A',
                    '1B' => '2B',
                    '1C' => '2C',
                    '1D' => '2D',
                ),
                1                    => array(
                    '1A' => '3A',
                    '1B' => '3B',
                    '1C' => '3C',
                    '1D' => '3D',
                ),
                2                    => array(
                    '1A' => '4A',
                    '1B' => '4B',
                    '1C' => '4C',
                    '1D' => '4D',
                ),
                3                    => array(
                    '1A' => '5A',
                    '1B' => '5B',
                    '1C' => '5C',
                    '1D' => '5D',
                ),
            ),
            DCsvFile::parse($stream)
        );
    }

    /**
     * @covers app\decibel\file\DCsvFile::parse
     */
    public function testparse_utf8()
    {
        $stream = new DFileStream($this->csvFixtureDir . 'utf8.csv');
        $this->assertSame(
            array(
                DCsvFile::KEY_HEADER => array(
                    'コラム1',
                    'コラム2',
                    'コラム3',
                ),
                0                    => array(
                    'コラム1' => '行1、列1',
                    'コラム2' => '行1、列2',
                    'コラム3' => '行1、列3',
                ),
                1                    => array(
                    'コラム1' => '行2、列1',
                    'コラム2' => '行2、列2',
                    'コラム3' => '行2、列3',
                ),
            ),
            DCsvFile::parse($stream)
        );
    }
}
