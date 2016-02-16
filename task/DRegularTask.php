<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

/**
 * Base class for regular scheduled tasks.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks
 */
abstract class DRegularTask extends DScheduledTask
{
    /**
     * Finalises the run process.
     * Could be overriden in other types of tasks.
     *
     * @return    void
     */
    public function finalise()
    {
        $nextTime = time() + ($this->getInterval() * 60);
        $this->reschedule($nextTime);
    }

    /**
     * Returns the number of minutes that should be waited for between
     * recurring executions of this task.
     *
     * @return    int        The number of minutes.
     */
    abstract public function getInterval();
}
