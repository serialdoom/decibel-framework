<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\health\DHealthCheck;
use app\decibel\health\DHealthCheckResult;

/**
 * Checks the current application mode.
 *
 * @author        Timothy de Paris
 */
class DApplicationModeHealthCheck extends DHealthCheck
{
    /**
     * Performs the health check.
     *
     * @return DHealthCheckResult[] List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                              objects.
     */
    public function checkHealth()
    {
        $results = array();
        // Notify about mode if not production.
        $currentMode = DApplicationMode::getMode();
        if ($currentMode !== DApplicationMode::MODE_PRODUCTION) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_MESSAGE,
                'Decibel is currently running in %s mode',
                array(DApplicationMode::getMode())
            );
        }

        return $results;
    }

    /**
     * Returns the name of the component being checked.
     *
     * @return    string
     */
    public function getComponentName()
    {
        return 'Application Mode';
    }
}
