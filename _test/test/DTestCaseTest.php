<?php
namespace tests\app\decibel\test;

use app\decibel\registry\DClassInfo;
use app\decibel\test\DTestCase;
use PHPUnit_Framework_TestCase;

/**
 * Class DTestCaseTest
 *
 * @package tests\app\decibel\test
 * @author Alex van Andel <avanandel@decibeltechnology.com>
 */
class DTestCaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers app\decibel\test\DTestCase::getMockForAbstractClass
     */
    public function testGetMockForAbstractClass()
    {
        $testCase = $this->getMockForAbstractClass(DTestCase::class);
        $mock = $testCase->getMockForAbstractClass(DTestCase::class);
        $classInfo = new DClassInfo(get_class($mock));
        $testCaseClassInfo = new DClassInfo(get_class($testCase));

        $this->assertSame('app\decibel\test', $classInfo->namespace);
        $this->assertNotSame($testCaseClassInfo->namespace, $classInfo->namespace);
        $this->assertTrue(class_exists($classInfo->getQualifiedName(), false));
    }
}
