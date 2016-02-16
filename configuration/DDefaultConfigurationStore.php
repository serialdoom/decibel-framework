<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;
use UnexpectedValueException;

/**
 * The default configuration store for a Decibel application.
 *
 * @author        Timothy de Paris
 */
class DDefaultConfigurationStore extends DConfigurationStore implements DSingleton
{
    use DSingletonClass;

    /**
     * Creates a {@link DDefaultConfigurationStore}
     *
     * @throws    UnexpectedValueException    If the store could not be created or loaded.
     */
    protected function __construct()
    {
        parent::__construct('', 'app.configuration');
    }
}
