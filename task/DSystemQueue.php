<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\task\DQueue;

/**
 * Default queue for Decibel applications.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_queued
 */
final class DSystemQueue extends DQueue
{
    /**
     * Returns the number of minutes that should be waited for between
     * recurring executions of this task.
     *
     * @return    int        The number of minutes.
     */
    public function getInterval()
    {
        return 1;
    }
}
