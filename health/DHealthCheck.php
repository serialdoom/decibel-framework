<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\health;

use app\decibel\authorise\DUser;

/**
 * Base class for health check functionality.
 *
 * Only leaf health check classes (i.e. those that are not extended by any
 * other class) will be executed. It is therefore very important to ensure
 * that the parent {@link checkHealth} function is executed when overriding,
 * so that checks are not lost.
 *
 * @author        Timothy de Paris
 */
abstract class DHealthCheck
{
    /**
     * Returns a new DHealthCheck.
     *
     * @return    static
     */
    public static function load()
    {
        return new static();
    }

    /**
     * Checks whether the provided user is able to execute this health check.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool
     */
    public function authorise(DUser $user)
    {
        return true;
    }

    /**
     * Performs the health check.
     *
     * @return    array    List of {@link DHealthCheckResult} objects.
     */
    abstract public function checkHealth();

    /**
     * Returns the name of the component being checked.
     *
     * @return    DLabel
     */
    abstract public function getComponentName();
}
