<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\model\event\DModelEvent;

/**
 * A model that can be cached in shared memory.
 *
 * @author        Timothy de Paris
 */
interface DCacheable
{
    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnUncache DOnUncache}
     * event.
     *
     * @var        string
     */
    const ON_UNCACHE = 'app\\decibel\\model\\event\DOnUncache';

    /**
     * Performs any uncaching operations neccessary when a model's data is changed to ensure
     * consitency across the application.
     *
     * @param    DModelEvent $event The event that required uncaching of the model.
     *
     * @return    void
     */
    public function uncache(DModelEvent $event = null);
}
