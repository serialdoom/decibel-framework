<?php
namespace tests\app\decibel\model\utilisation;

use app\decibel\model\utilisation\DUtilisationRecord;
use app\decibel\test\DTestCase;

/**
 * Test class for DUtilisationRecord.
 * Generated by Decibel on 2011-10-31 at 14:20:37.
 */
class DUtilisationRecordTest extends DTestCase
{
    /**
     * @covers app\decibel\model\utilisation\DUtilisationRecord::getRebuildTaskName
     */
    public function testgetRebuildTaskName()
    {
        $this->assertSame('app\\decibel\\model\\utilisation\\DRebuildUtilisationIndex',
                          DUtilisationRecord::getRebuildTaskName());
    }
}
