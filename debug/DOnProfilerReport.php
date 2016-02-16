<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\event\DEvent;
use app\decibel\regional\DLabel;

/**
 * Event triggered when the profiler issues a report.
 *
 * @author        Timothy de Paris
 */
class DOnProfilerReport extends DEvent
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
