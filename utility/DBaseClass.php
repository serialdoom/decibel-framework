<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DDeprecatedException;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidMethodException;
use app\decibel\debug\DInvalidPropertyException;

/**
 * Provides base implementations of PHP magic methods.
 *
 * @author    Timothy de Paris
 */
trait DBaseClass
{
    /**
     * Emmulates a method call.
     *
     * @param    string $name Name of the method to call.
     * @param    array  $args Arguments to pass to the method.
     *
     * @return    mixed
     * @throws    DInvalidMethodException    If no method exists with
     *                                    the provided name.
     */
    public function __call($name, array $args)
    {
        throw new DInvalidMethodException(array(get_class($this), $name));
    }

    /**
     * Retrieves object parameters.
     *
     * @param    string $name The parameter name.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If no property exists with
     *                                        the specified name.
     */
    public function __get($name)
    {
        throw new DInvalidPropertyException($name);
    }

    /**
     * Sets object parameters.
     *
     * @param    string $name  The parameter name.
     * @param    mixed  $value The new value.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If no property exists with
     *                                        the specified name.
     */
    public function __set($name, $value)
    {
        throw new DInvalidPropertyException($name);
    }

    /**
     * Throws a {@link DDeprecatedException} exception if the current configuration
     * does not allow magic property access.
     *
     * @param    string $property    Name of the property being accessed.
     * @param    string $replacement Replacement for the deprecated property, if applicable.
     *
     * @return    void
     * @throws    DDeprecatedException
     * @deprecated
     */
    protected function notifyDeprecatedPropertyAccess($property, $replacement = null)
    {
        $class = get_class($this);
        if ($replacement !== null) {
            $replacement = "{$class}::{$replacement}";
        } else {
            $replacement = 'N/A';
        }
        $deprecated = "{$class}::\${$property}";
        DErrorHandler::notifyDeprecation($deprecated, $replacement);
    }
}
