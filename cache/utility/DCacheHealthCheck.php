<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache\utility;

use app\decibel\cache\DCache;
use app\decibel\cache\utility\DCacheStatistics;
use app\decibel\health\DHealthCheck;
use app\decibel\regional\DLabel;

/**
 * Checks the health of debugging functions.
 *
 * @author        Timothy de Paris
 */
class DCacheHealthCheck extends DHealthCheck
{
    /**
     * Performs the health check.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                    objects.
     */
    public function checkHealth()
    {
        $cache = DCache::load();
        $statistics = DCacheStatistics::adapt($cache);

        return $statistics->checkHealth();
    }

    /**
     * Returns the name of the component being checked.
     *
     * @return    DLabel
     */
    public function getComponentName()
    {
        return new DLabel(self::class, 'componentName');
    }
}
