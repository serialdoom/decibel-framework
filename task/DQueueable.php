<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

/**
 * A class that can be queued for processing within a {@link DQueue}.
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_queued
 */
interface DQueueable
{
    /**
     * Returns the priority of this queueable item.
     *
     * Items will be processed based on a combination of their age and priority.
     *
     * @return    int        Priority where <code>0</code> is the highest priority.
     */
    public function getPriority();
}
