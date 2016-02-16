<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\event\DEvent;
use app\decibel\regional\DLabel;

/**
 * Event triggered when Decibel configuration options are changed.
 *
 * @author        Timothy de Paris
 */
class DOnConfigurationChange extends DEvent
{
    /**
     * Returns a human-readable description for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        return null;
    }

    /**
     * Returns a human-readable name for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return null;
    }
}
