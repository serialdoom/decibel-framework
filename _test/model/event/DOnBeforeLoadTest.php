<?php
namespace tests\app\decibel\model\event;

use app\decibel\model\event\DOnBeforeLoad;
use app\decibel\test\DTestCase;

/**
 * Test class for DOnBeforeLoad.
 * Generated by Decibel on 2012-04-12 at 09:08:49.
 */
class DOnBeforeLoadTest extends DTestCase
{
    /**
     * @covers app\decibel\model\event\DOnBeforeLoad
     */
    public function test__construct()
    {
        $event = new DOnBeforeLoad();
        $this->assertInstanceOf('app\\decibel\\model\\event\\DOnBeforeLoad', $event);
    }
}
