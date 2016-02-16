<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Profiler class used when no profiling is required.
 *
 * @author        Timothy de Paris
 */
class DBasicProfiler extends DProfiler
{
    /**
     * Continues a previously stopped activity. Using this function instead
     * of startActivity will resume profiling of the activity without
     * increasing it's iteration count. The activity will be started if
     * not yet registered.
     *
     * @param    string $name The name of the activity.
     *
     * @return    void
     */
    public function continueActivity($name)
    { }

    /**
     * Registers the start of a particular activity.
     *
     * @param    string $name         Name of the activity.
     * @param    float  $startTime    Time from which this activity should
     *                                start. If not provided, the current
     *                                microtime will be used.
     *
     * @return    void
     */
    public function startActivity($name, $startTime = null)
    { }

    /**
     * Registers the end of a particular activity.
     *
     * @param    string $name The name of the activity.
     *
     * @return    void
     */
    public function stopActivity($name)
    { }
}
