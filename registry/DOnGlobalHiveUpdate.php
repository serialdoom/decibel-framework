<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\regional\DLabel;

/**
 * Event triggered when a global registry hive is updated.
 *
 * @author        Timothy de Paris
 */
class DOnGlobalHiveUpdate extends DOnHiveUpdate
{
    /**
     * Defines parameters available for this event.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Returns a human-readable description for the event.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        return null;
    }

    /**
     * Returns a human-readable name for the event.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return null;
    }
}
