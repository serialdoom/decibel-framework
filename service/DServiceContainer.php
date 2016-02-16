<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\service;

use app\decibel\configuration\DConfigurable;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DResult;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Base class for access to external services.
 *
 * The {@link DServiceContainer} class is the base class for dependency
 * injection containers within Decibel.
 *
 * @author        Timothy de Paris
 */
abstract class DServiceContainer implements DSingleton, DConfigurable
{
    use DBaseClass;
    use DSingletonClass;

    /**
     * Checks if all functionality required to use the service
     * is currently available.
     *
     * @return    DResult
     */
    abstract public function test();
}
