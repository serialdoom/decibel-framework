<?php
namespace tests\app\decibel\registry;

use app\decibel\registry\DClassInfo;
use app\decibel\test\DTestCase;

/**
 * Class DClassInfoTest
 * @package tests\app\decibel\registry
 */
class DClassInfoTest extends DTestCase
{
    /**
     * @covers app\decibel\registry\DClassInfo::__construct
     */
    public function testCreate()
    {
        $class = new DClassInfo('app\my\TestClass');
        $this->assertSame('TestClass', $class->className);
        $this->assertSame('app\my', $class->namespace);
    }

    /**
     * @covers app\decibel\registry\DClassInfo::getNamespace
     */
    public function testGetNamespace()
    {
        $class = new DClassInfo('app\my\TestClass');
        $this->assertEquals('app\my', $class->getNamespace());
    }

    /**
     * @covers app\decibel\registry\DClassInfo::getNamespace
     */
    public function testGetNamespacePreserveGlobalNs()
    {
        $class = new DClassInfo('app\my\TestClass');
        $this->assertSame('app\my', $class->getNamespace(false));
        $class->namespace = NAMESPACE_SEPARATOR . $class->namespace;
        $this->assertSame('app\my', $class->getNamespace(false));
        $this->assertSame('\app\my', $class->getNamespace());
    }

    /**
     * @covers app\decibel\registry\DClassInfo::getQualifiedName
     */
    public function testGetQualifiedName()
    {
        $qualifiedName = 'app\my\TestClass';
        $class = new DClassInfo($qualifiedName);
        $this->assertSame($qualifiedName, $class->getQualifiedName());
    }
}
