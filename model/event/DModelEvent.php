<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\event;

use app\decibel\event\DEvent;

/**
 * Base class for events triggered by models.
 *
 * @author        Timothy de Paris
 */
abstract class DModelEvent extends DEvent
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

    /**
     * Returns the model instance that triggered this event.
     *
     * @return    DBaseModel
     */
    public function getModelInstance()
    {
        return $this->dispatcher;
    }
}
