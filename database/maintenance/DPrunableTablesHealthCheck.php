<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\maintenance;

use app\decibel\health\DHealthCheck;
use app\decibel\health\DHealthCheckResult;
use app\decibel\regional\DLabel;

/**
 * Checks the health of the database.
 *
 * @author        Timothy de Paris
 */
class DPrunableTablesHealthCheck extends DHealthCheck
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
        $databasePruner = new DDatabasePruner();
        $prunableFieldCount = count($databasePruner->getPrunableFields());
        if ($prunableFieldCount > 0) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_WARNING,
                new DLabel(self::class, 'errorPrunableFields', array(
                    'prunableFieldCount' => $prunableFieldCount,
                )),
                array($prunableFieldCount)
            );
        }
        $prunableTableCount = count($databasePruner->getPrunableTables());
        if ($prunableTableCount > 0) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_WARNING,
                new DLabel(self::class, 'errorPrunableTables', array(
                    'prunableTableCount' => $prunableTableCount,
                )),
                array($prunableTableCount)
            );
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
        return new DLabel(self::class, 'name');
    }
}
