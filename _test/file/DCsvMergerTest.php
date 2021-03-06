<?php
namespace tests\app\decibel\file;

use app\decibel\file\DCsvFile;
use app\decibel\file\DCsvMerger;
use app\decibel\stream\DFileStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DCsvMerger.
 * Generated by Decibel on 2011-10-31 at 14:09:44.
 */
class DCsvMergerTest extends DTestCase
{
    private $csvFixtureDir;

    public function __construct()
    {
        $this->csvFixtureDir = __DIR__ . '/../_fixtures/csv/';
    }

    /**
     * @covers app\decibel\file\DCsvMerger::__construct
     * @covers app\decibel\file\DCsvMerger::generateHeader
     * @covers app\decibel\file\DCsvMerger::generateIndex
     * @covers app\decibel\file\DCsvMerger::generateKeyList
     * @covers app\decibel\file\DCsvMerger::merge
     */
    public function testmerge()
    {
        $stream1 = new DFileStream($this->csvFixtureDir . 'merge1.csv');
        $stream2 = new DFileStream($this->csvFixtureDir . 'merge2.csv');
        $csvData1 = DCsvFile::parse($stream1);
        $csvData2 = DCsvFile::parse($stream2);
        $merger = new DCsvMerger('Column 2');
        $this->assertSame(
            array(
                DCsvFile::KEY_HEADER => array(
                    0 => 'Column 2',
                    1 => 'Column 1',
                    2 => 'Column 3',
                ),
                0                    => array(
                    'Column 1' => 'Row 1, Column 1',
                    'Column 2' => 'Row 1, Column 2',
                    'Column 3' => 'Row 1, Column 3',
                ),
                1                    => array(
                    'Column 1' => 'Row 2, Column 1',
                    'Column 2' => 'Row 2, Column 2',
                    'Column 3' => 'Row 2, Column 3',
                ),
            ),
            $merger->merge($csvData1, $csvData2)
        );
    }

    /**
     * @covers app\decibel\file\DCsvMerger::__construct
     * @covers app\decibel\file\DCsvMerger::generateHeader
     * @covers app\decibel\file\DCsvMerger::generateIndex
     * @covers app\decibel\file\DCsvMerger::generateKeyList
     * @covers app\decibel\file\DCsvMerger::merge
     */
    public function testmerge_rightJoin()
    {
        $stream1 = new DFileStream($this->csvFixtureDir . 'merge1.csv');
        $stream2 = new DFileStream($this->csvFixtureDir . 'merge2.csv');
        $csvData1 = DCsvFile::parse($stream1);
        $csvData2 = DCsvFile::parse($stream2);
        $merger = new DCsvMerger('Column 2', true);
        $this->assertSame(
            array(
                DCsvFile::KEY_HEADER => array(
                    0 => 'Column 2',
                    1 => 'Column 1',
                    2 => 'Column 3',
                ),
                0                    => array(
                    'Column 1' => 'Row 1, Column 1',
                    'Column 2' => 'Row 1, Column 2',
                    'Column 3' => 'Row 1, Column 3',
                ),
                1                    => array(
                    'Column 1' => 'Row 2, Column 1',
                    'Column 2' => 'Row 2, Column 2',
                    'Column 3' => 'Row 2, Column 3',
                ),
                2                    => array(
                    'Column 1' => '',
                    'Column 2' => 'Row 3, Column 2',
                    'Column 3' => 'Row 3, Column 3',
                ),
            ),
            $merger->merge($csvData1, $csvData2)
        );
    }
}
