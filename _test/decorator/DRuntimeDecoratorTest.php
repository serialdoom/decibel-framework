<?php
namespace tests\app\decibel\decorator;

use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\test\DTestCase;

/**
 * Test class for DEventSubscription.
 * Generated by Decibel on 2011-10-31 at 14:08:29.
 */
class DRuntimeDecoratorTest extends DTestCase
{
    /**
     * @covers app\decibel\decorator\DRuntimeDecorator::decorate
     * @expectedException app\decibel\decorator\DMissingDecoratorException
     */
    public function testdecorate()
    {
        $object = new TestDDecoratable();
        $decorator = new TestDDecorator($object);
        $this->assertInstanceOf('app\\decibel\\decorator\\DDecorator', DRuntimeDecorator::decorate($object));
    }
}
