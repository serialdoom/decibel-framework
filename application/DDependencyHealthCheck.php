<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\application\DAppManager;
use app\decibel\health\DHealthCheck;
use app\decibel\regional\DLabel;

/**
 * Checks whether all dependencies are met.
 *
 * @author        Timothy de Paris
 */
class DDependencyHealthCheck extends DHealthCheck
{
    /**
     * Performs the health check.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult}
     *                    objects.
     */
    public function checkHealth()
    {
        $results = array();
        $appManager = DAppManager::load();
        foreach ($appManager->getApps() as $app) {
            /* @var $app DApp */
            $manifest = $app->getManifest();
            $results = array_merge($results, $manifest->checkDependencies());
        }

        return $results;
    }

    /**
     * Returns the name of the component being checked.
     *
     * @return    DLabel
     */
    public function getComponentName()
    {
        return new DLabel(DAppManager::class, 'environment');
    }
}
