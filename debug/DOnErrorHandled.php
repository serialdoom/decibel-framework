<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\event\DEvent;
use app\decibel\regional\DLabel;

/**
 * Event triggered when an error is handled by {@link DErrorHandler}.
 *
 * @author    Timothy de Paris
 */
class DOnErrorHandled extends DEvent
{
    /**
     * The error that occurred.
     *
     * @var        DError
     */
    private $error;

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

    /**
     * Returns the error that was handled.
     *
     * @return    DError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Sets the error to be handled.
     *
     * @param    DError $error The error.
     *
     * @return    static
     */
    public function setError(DError $error)
    {
        $this->error = $error;

        return $this;
    }
}
