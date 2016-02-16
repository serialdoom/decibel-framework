<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\regional\DLabel;

/**
 * Handles an exception occurring within the class information registry.
 *
 * @author        Timothy de Paris
 */
abstract class DClassInformationException extends DRegistryException
{
    /**
     * Generates the exception message, using the provided variables.
     *
     * @param    array $variables
     *
     * @return    DLabel
     */
    protected function generateMessage(array $variables)
    {
        if (defined('static::MESSAGE')) {
            $message = vsprintf(static::MESSAGE, $variables);
        } else {
            $message = parent::generateMessage($variables);
        }

        return $message;
    }
}
