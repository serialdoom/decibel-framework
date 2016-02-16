<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\event\DEvent;
use app\decibel\regional\DLabel;

/**
 * Event triggered when a scheduled event fails during execution.
 *
 * @note
 * This event is triggered when the
 * {@link app::decibel::task::DScheduledTask::execute() DScheduledTask::execute()}
 * method throws an exception.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_events
 */
class DOnTaskFail extends DEvent
{
    /**
     * Defines parameters available for this event.
     *
     * @return    void
     */
    protected function define()
    { }

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
