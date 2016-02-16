<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\event;

use app\decibel\authorise\DUser;
use app\decibel\model\event\DModelEvent;

/**
 * Parent class for events triggered on user events.
 *
 * @author        Timothy de Paris
 */
abstract class DUserEvent extends DModelEvent
{
    /**
     * Returns the user this event was triggered for.
     *
     * @return    DUser
     */
    public function getUser()
    {
        return $this->dispatcher;
    }
}
