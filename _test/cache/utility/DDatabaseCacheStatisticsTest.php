<?php
namespace tests\app\decibel\cache\utility;

use app\decibel\cache\DDatabaseCache;
use app\decibel\cache\utility\DCacheStatistics;
use app\decibel\cache\utility\DDatabaseCacheStatistics;
use app\decibel\health\DHealthCheckResult;
use app\decibel\test\DTestCase;

/**
 * Class DDatabaseCacheStatisticsTest
 * @package tests\app\decibel\cache\utility
 */
class DDatabaseCacheStatisticsTest extends DTestCase
{
    /**
     * @covers app\decibel\cache\utility\DCacheStatistics::adapt
     */
    public function testAdapt()
    {
        $cache = DDatabaseCache::load();
        $statistics = DCacheStatistics::adapt($cache);
        $this->assertInstanceOf(DDatabaseCacheStatistics::class, $statistics);
    }

    /**
     * @covers app\decibel\cache\utility\DDatabaseCacheStatistics::checkHealth
     */
    public function testCheckHealth()
    {
        $cache = DDatabaseCache::load();
        /** @var DDatabaseCacheStatistics $statistics */
        $statistics = DCacheStatistics::adapt($cache);
        /** @var DHealthCheckResult[] $results */
        $results = $statistics->checkHealth();
        $this->assertContainsOnlyInstancesOf(DHealthCheckResult::class, $results);
        $this->assertCount(1, $results);
        $this->assertSame(
            'No shared memory cache configured. This can have a serious affect on application performance.',
            $results[0]->getDescription());
    }

    /**
     * @covers app\decibel\cache\utility\DDatabaseCacheStatistics::getAdaptableClass
     */
    public function testGetAdaptableClass()
    {
        $this->assertSame(DDatabaseCache::class, DDatabaseCacheStatistics::getAdaptableClass());
    }

    /**
     * @covers app\decibel\cache\utility\DDatabaseCacheStatistics::getStatistics
     */
    public function testStatistics()
    {
        $cache = DDatabaseCache::load();
        /** @var DDatabaseCacheStatistics $statistics */
        $statistics = DCacheStatistics::adapt($cache);
        $this->assertEmpty($statistics->getStatistics());
    }
}
