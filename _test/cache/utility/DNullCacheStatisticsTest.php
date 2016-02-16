<?php
namespace tests\app\decibel\cache\utility;

use app\decibel\cache\DNullCache;
use app\decibel\cache\utility\DCacheStatistics;
use app\decibel\cache\utility\DNullCacheStatistics;
use app\decibel\health\DHealthCheckResult;
use app\decibel\test\DTestCase;

/**
 * Class DNullCacheStatisticsTest
 * @package tests\app\decibel\cache\utility
 */
class DNullCacheStatisticsTest extends DTestCase
{
    /**
     * @covers app\decibel\cache\utility\DCacheStatistics::adapt
     */
    public function testAdapt()
    {
        $cache = DNullCache::load();
        $statistics = DCacheStatistics::adapt($cache);
        $this->assertInstanceOf(DNullCacheStatistics::class, $statistics);
    }

    /**
     * @covers app\decibel\cache\utility\DNullCacheStatistics::checkHealth
     */
    public function testCheckHealth()
    {
        $cache = DNullCache::load();
        /** @var DNullCacheStatistics $statistics */
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
     * @covers app\decibel\cache\utility\DNullCacheStatistics::getAdaptableClass
     */
    public function testGetAdaptableClass()
    {
        $this->assertSame(DNullCache::class, DNullCacheStatistics::getAdaptableClass());
    }

    /**
     * @covers app\decibel\cache\utility\DNullCacheStatistics::getStatistics
     */
    public function testStatistics()
    {
        $cache = DNullCache::load();
        /** @var DNullCacheStatistics $statistics */
        $statistics = DCacheStatistics::adapt($cache);
        $this->assertEmpty($statistics->getStatistics());
    }
}
