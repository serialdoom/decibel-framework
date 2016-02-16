<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

/**
 * Base class for nightly scheduled tasks.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks
 */
abstract class DNightlyTask extends DScheduledTask
{
    /**
     * Finalises the run process.
     * Could be overriden in other types of tasks.
     *
     * @return    void
     */
    public function finalise()
    {
        // Reschedule task
        $configuration = DTaskConfiguration::load();
        $nextTime = mktime($configuration->getNightlyTaskHour(), 0, 0, date('m'), date('d') + 1);
        $this->reschedule($nextTime);
    }
}
