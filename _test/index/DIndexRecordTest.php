<?php
namespace tests\app\decibel\index;

use app\decibel\index\DIndexRecord;
use app\decibel\model\field\DTextField;
use app\decibel\test\DTestCase;

class TestIndexRecord extends DIndexRecord
{
    public static function getDisplayName()
    {
        return 'Test Index Record';
    }

    public static function getDisplayNamePlural()
    {
        return 'Test Index Records';
    }

    protected function define()
    {
        $test1 = new DTextField('test1', 'Test 1');
        $this->addField($test1);
    }
}

/**
 * Test class for DIndexRecord.
 * Generated by Decibel on 2012-04-12 at 09:54:12.
 */
class DIndexRecordTest extends DTestCase
{
    /**
     * @covers app\decibel\index\DIndexRecord::delete
     */
    public function testdelete()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $record = TestIndexRecord::load('tests\\app\\decibel\\index\\TestIndexRecord');
        $result = $record->delete();
        $this->assertInstanceOf('app\\decibel\\utility\\DResult', $result);
        $this->assertFalse($result->isSuccessful());
    }

    /**
     * @covers app\decibel\index\DIndexRecord::getRebuildTaskName
     * @expectedException app\decibel\debug\DNotImplementedException
     */
    public function testgetRebuildTaskName()
    {
        DIndexRecord::getRebuildTaskName();
    }

    /**
     * @covers app\decibel\index\DIndexRecord::getMaximumIndexSize
     * @expectedException app\decibel\debug\DNotImplementedException
     */
    public function testgetMaximumIndexSize()
    {
        DIndexRecord::getMaximumIndexSize();
    }
}
