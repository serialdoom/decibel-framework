<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\task\DRegularTask;

/**
 * Base class for task queues.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_queued
 */
abstract class DQueue extends DRegularTask
{
    /**
     * Processes the next item in the queue.
     *
     * @return    void
     */
    protected function execute()
    { }

    /**
     * Adds a task to the queue.
     *
     * @param    DQueueableTask $task The task to place in the queue.
     *
     * @return    DQueueResult
     */
    public function enqueue(DQueueableTask $task)
    { }
}
