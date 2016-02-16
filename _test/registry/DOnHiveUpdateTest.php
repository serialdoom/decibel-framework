<?php
namespace tests\app\decibel\registry;

use app\decibel\registry\DOnHiveUpdate;
use app\decibel\test\DTestCase;
use PHPUnit_Framework_TestCase;

/**
 * Test class for DOnHiveUpdate.
 * Generated by Decibel on 2011-10-31 at 14:12:38.
 */
class DOnHiveUpdateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers app\decibel\registry\DOnHiveUpdate::getDescription
     */
    public function testGetDescription()
    {
        $event = new DOnHiveUpdate();
        $this->assertNull($event->getDescription());
    }

    /**
     * @covers app\decibel\registry\DOnHiveUpdate::getDisplayName
     */
    public function testGetDisplayName()
    {
        $event = new DOnHiveUpdate();
        $this->assertNull($event->getDisplayName());
    }
}
