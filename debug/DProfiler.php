<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\utility\DBaseClass;

/**
 * Allows profiling of Decibel requests.
 *
 * @author        Timothy de Paris
 */
abstract class DProfiler implements DDispatchable, DDecoratable
{
    use DBaseClass;
    use DDecoratorCache;
    use DEventDispatcher;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::debug::DOnProfilerReport DOnProfilerReport}
     * event.
     *
     * @var        string
     */
    const ON_PROFILER_REPORT = DOnProfilerReport::class;

    /**
     * Constant defined when full profiling is enabled.
     *
     * @var        string
     */
    const PROFILER_ENABLED = 'DECIBEL_PROFILE';

    /**
     *
     * @var        DProfiler
     */
    private static $profiler;

    /**
     * Whether the profiler is running.
     *
     * @var        bool
     */
    private $isRunning = false;

    /**
     * Request execution start time.
     *
     * @var        float
     */
    private $startTime;

    /**
     * Loads the enabled profiler and returns it.
     *
     * @return    DProfiler
     */
    public static function load()
    {
        if (self::$profiler === null) {
            if (ini_get('decibel.profile')
                || array_key_exists('DECIBEL_PROFILE', $_GET)
            ) {
                self::$profiler = new DFullProfiler();
                define(self::PROFILER_ENABLED, true);
            } else {
                self::$profiler = new DBasicProfiler();
            }
        }

        return self::$profiler;
    }

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
    abstract public function continueActivity($name);

    /**
     * Returns the running time of this request
     * up to the current execution point.
     *
     * @return    float    Execution time (in seconds).
     */
    public function getCurrentExecutionTime()
    {
    }

    /**
     * Returns the peak memory utilisation of this request
     * up to the current execution point.
     *
     * @return    float    Peak memory utilisation (in MB).
     */
    public function getCurrentMemoryUtilisation()
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return self::ON_PROFILER_REPORT;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_PROFILER_REPORT,
        );
    }

    /**
     * Checks if the profiler is running.
     *
     * @return    bool
     */
    public function isRunning()
    {
        return $this->isRunning;
    }

    /**
     * Starts the profiler.
     *
     * @param    float $startTime     The time from which this profiling should
     *                                start. If no provided, the current
     *                                microtime will be used.
     *
     * @return    void
     */
    public function start($startTime = null)
    {
        if ($startTime === null) {
            $startTime = microtime();
        }
        $this->startTime = $startTime;
        $this->isRunning = true;
    }

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
    abstract public function startActivity($name, $startTime = null);

    /**
     * Stops the profiler.
     *
     * @return    void
     */
    public function stop()
    {
        $this->isRunning = false;
    }

    /**
     * Registers the end of a particular activity.
     *
     * @param    string $name The name of the activity.
     *
     * @return    void
     */
    abstract public function stopActivity($name);

    /**
     * Continues profiling of an activity.
     *
     * @param    string $activity Name of the activity being profiled.
     *
     * @return    void
     */
    public static function continueProfiling($activity)
    {
        if (defined(self::PROFILER_ENABLED)) {
            self::$profiler->continueActivity($activity);
        }
    }

    /**
     * Starts profiling of an activity.
     *
     * @param    string $activity Name of the activity being profiled.
     *
     * @return    void
     */
    public static function startProfiling($activity)
    {
        if (defined(self::PROFILER_ENABLED)) {
            self::$profiler->startActivity($activity);
        }
    }

    /**
     * Stops profiling of an activity.
     *
     * @param    string $activity Name of the activity being profiled.
     *
     * @return    void
     */
    public static function stopProfiling($activity)
    {
        if (defined(self::PROFILER_ENABLED)) {
            self::$profiler->stopActivity($activity);
        }
    }
}
