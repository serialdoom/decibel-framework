<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DRecursionException;

/**
 * Implementation of a singleton class.
 *
 * @author        Timothy de Paris
 */
trait DSingletonClass
{
    /**
     * Stores references to loaded singleton instances.
     *
     * @var        array
     */
    protected static $instances = array();

    /**
     * Initialises the singleton instance.
     *
     * @return    static
     */
    abstract protected function __construct();

    /**
     * Performs functions required to complete initialisation of the instance.
     *
     * @return    void
     */
    public function __wakeup()
    {
    }

    /**
     * Returns the qualified name of the singleton class to be loaded.
     *
     * This allows an inheriting class to load a class other than itself (such as a child class)
     * if this is deemed appropriate for the executing scenario.
     *
     * @return    string
     */
    protected static function getSingletonClass()
    {
        return get_called_class();
    }

    /**
     * Returns a singleton instance of the extending class.
     *
     * @return    static
     * @throws    DRecursionException    If a recursive load scenario is detected.
     */
    public static function load()
    {
        $qualifiedName = static::getSingletonClass();
        // Load if this is the first instantiation.
        if (!isset(static::$instances[ $qualifiedName ])) {
            // Note that this is loading to detect recursion.
            static::$instances[ $qualifiedName ] = true;
            // Load the instance.
            static::$instances[ $qualifiedName ] = new $qualifiedName();
            static::$instances[ $qualifiedName ]->__wakeup();
        }
        // Handle recursion.
        if (static::$instances[ $qualifiedName ] === true) {
            throw new DRecursionException(array($qualifiedName, 'load'));
        }

        return static::$instances[ $qualifiedName ];
    }

    /**
     * Determines if the singleton class is currently loading.
     *
     * @return    bool
     */
    public static function isLoading()
    {
        $qualifiedName = static::getSingletonClass();

        return isset(static::$instances[ $qualifiedName ])
        && static::$instances[ $qualifiedName ] === true;
    }

    /**
     * Determines if a singleton class has already been loaded.
     *
     * @return    bool
     */
    public static function isLoaded()
    {
        $qualifiedName = static::getSingletonClass();

        return isset(static::$instances[ $qualifiedName ])
        && is_object(static::$instances[ $qualifiedName ]);
    }
}
