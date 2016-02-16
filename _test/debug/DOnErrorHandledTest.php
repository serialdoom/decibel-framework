<?php
namespace tests\app\decibel\debug;

use app\decibel\debug\DError;
use app\decibel\debug\DOnErrorHandled;
use app\decibel\test\DTestCase;
use app\decibel\utility\DUtilityData;

/**
 * Test class for DOnErrorHandled.
 * Generated by Decibel on 2012-04-12 at 09:08:13.
 */
class DOnErrorHandledTest extends DTestCase
{
    /**
     * @covers app\decibel\debug\DOnErrorHandled::getDescription
     */
    public function testgetDescription()
    {
        $this->assertNull(DOnErrorHandled::getDescription());
    }

    /**
     * @covers app\decibel\debug\DOnErrorHandled::getDisplayName
     */
    public function testgetDisplayName()
    {
        $this->assertNull(DOnErrorHandled::getDisplayName());
    }

    /**
     * @covers app\decibel\debug\DOnErrorHandled::setError
     * @covers app\decibel\debug\DOnErrorHandled::getError
     */
    public function testsetError()
    {
        $event = new DOnErrorHandled();
        $error = new DError(DError::TYPE_ASSERTION);
        $this->assertSame($event, $event->setError($error));
        $this->assertSame($error, $event->getError($error));
    }
}