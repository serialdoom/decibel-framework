<?php
namespace tests\app\decibel\adapter;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DAdapterCache;
use app\decibel\adapter\DStaticAdapter;
use app\decibel\test\DTestCase;

class TestDAdapterCache implements DAdaptable
{
    use DAdapterCache;
}

class TestDAdapter implements DAdapter
{
    use DStaticAdapter;

    public function __construct()
    {
    }

    public static function getAdaptableClass()
    {
        return 'tests\\app\\decibel\\adapter\\TestDAdapterCache';
    }
}

/**
 * Test class for DAdapterCache.
 */
class DAdapterCacheTest extends DTestCase
{
    /**
     * @covers app\decibel\adapter\DAdapterCache::getAdapter
     * @covers app\decibel\adapter\DAdapterCache::setAdapter
     */
    public function testgetAdapter()
    {
        $adapterCache = new TestDAdapterCache();
        $adapter = new TestDAdapter();
        $this->assertNull($adapterCache->getAdapter('tests\\app\\decibel\\adapter\\TestDAdapter'));
        $this->assertNull($adapterCache->setAdapter($adapter));
        $this->assertSame($adapter, $adapterCache->getAdapter('tests\\app\\decibel\\adapter\\TestDAdapter'));
    }
}
