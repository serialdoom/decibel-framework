<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache\utility;

use app\decibel\cache\DNullCache;
use app\decibel\cache\utility\DCacheStatistics;
use app\decibel\health\DHealthCheckResult;

/**
 * Provides functionality to report on the operation of a {@link DNullCache}.
 *
 * @author    Timothy de Paris
 */
class DNullCacheStatistics extends DCacheStatistics
{
    /**
     * Performs a health check on this share memory cache.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                    objects.
     */
    public function checkHealth()
    {
        $results = array();
        $results[] = new DHealthCheckResult(
            DHealthCheckResult::HEALTH_CHECK_ERROR,
            'No shared memory cache configured. This can have a serious affect on application performance.'
        );

        return $results;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DNullCache::class;
    }

    /**
     * Returns statistical information about the memory cache.
     *
     * Although there is no requirement for specific values to be returned, the following
     * information is useful for cache tuning and should be returned if supported by the cache:
     * - <strong>Uptime</strong>: Period of time that the cache has been available.
     * - <strong>Utilisation</strong>: Percentage of the total allocated storage space currently used.
     * - <strong>Capacity</strong>: Total amount of data it is possible for the cache to store.
     * - <strong>Input</strong>: Number of items and the total size of data added to the cache.
     * - <strong>Ouput</strong>: Number of items and the total size of data retrieved from the cache.
     * - <strong>Hit Rate</strong>: Ratio of successful to unsuccessful requests to the cache.
     * - <strong>I/O Ratio</strong>: Ratio of items retrieved to items added.
     * - <strong>Eviction Ratio</strong>: Ratio of items removed from the cache to items added.
     *
     * @return    array    Key/value statistical pairs. Both key and value should
     *                    be human readable descriptive values.
     */
    public function getStatistics()
    {
        return array();
    }
}
