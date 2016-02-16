<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Denotes a class as having the ability to be debugged
 * by the {@link DErrorHandler}.
 *
 * @author        Timothy de Paris
 */
interface DDebuggable
{
    /**
     * Provides debugging output for this object.
     *
     * This function must return a multi-dimensional array containing
     * key/value pairs of object properties to be debugged.
     *
     * @return    array
     */
    public function generateDebug();
}
