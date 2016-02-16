<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

/**
 * A model that can be cached in process memory.
 *
 * @author        Timothy de Paris
 */
interface DProcessCacheable
{
    /**
     * Clears the cached reference to this model allowing associated memory to be freed.
     *
     * @return    void
     */
    public function free();
}
