<?php
namespace tests\app\decibel\utility;

use app\decibel\test\DTestCase;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

class TestSingleton implements DSingleton
{
    use DSingletonClass;

    protected function __construct()
    {
    }
}

class TestRecursiveSingleton implements DSingleton
{
    use DSingletonClass;

    protected function __construct()
    {
        TestRecursiveSingleton::load();
    }
}

/**
 * Test class for DSingletonClass.
 * Generated by Decibel on 2011-10-31 at 14:13:32.
 */
class DSingletonClassTest extends DTestCase
{
    /**
     * Important for this test to be first!
     *
     * @covers app\decibel\utility\DSingletonClass::load
     * @covers app\decibel\utility\DSingletonClass::isLoaded
     */
    public function testIsLoaded()
    {
        $this->assertFalse(TestSingleton::isLoaded());
        $this->assertInstanceOf('tests\\app\\decibel\\utility\\TestSingleton', TestSingleton::load());
        $this->assertTrue(TestSingleton::isLoaded());
    }

    /**
     * @covers app\decibel\utility\DSingletonClass::load
     * @expectedException app\decibel\debug\DRecursionException
     */
    public function testLoadRecursive()
    {
        TestRecursiveSingleton::load();
    }
}
