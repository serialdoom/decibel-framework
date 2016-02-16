<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\cache\DCacheHandler;
use app\decibel\configuration\DApplicationMode;
use app\decibel\database\DQuery;
use app\decibel\router\DRouter;

/**
 * Default Decibel profiler.
 *
 * @author        Timothy de Paris
 */
class DFullProfiler extends DProfiler
{
    /**
     * 'Total' activity name.
     *
     * @var        string
     */
    const ACTIVITY_TOTAL = 'Total';
    /**
     * 'Cache' activity point.
     *
     * Used to store profiling information about cache activity.
     *
     * @var        string
     */
    const ACTIVITY_POINT_CACHE = 'c';
    /**
     * 'Model' activity point.
     *
     * Used to store profiling information about loaded models.
     *
     * @var        string
     */
    const ACTIVITY_POINT_MODEL = 'm';
    /**
     * 'Files' activity point.
     *
     * Used to store profiling information about loaded PHP source files.
     *
     * @var        string
     */
    const ACTIVITY_POINT_FILES = 'f';
    /**
     * 'Start' activity point.
     *
     * @var        string
     */
    const ACTIVITY_POINT_START = 's';
    /**
     * 'Start' activity point.
     *
     * @var        string
     */
    const ACTIVITY_POINT_END = 't';
    /**
     * 'Iterations' activity value.
     *
     * @var        string
     */
    const ACTIVITY_VALUE_ITERATIONS = 'i';
    /**
     * 'Memory' activity value.
     *
     * @var        string
     */
    const ACTIVITY_VALUE_MEMORY = 'mem';
    /**
     * 'Queries' activity value.
     *
     * @var        string
     */
    const ACTIVITY_VALUE_QUERIES = 'sql';
    /**
     * 'Time' activity value.
     *
     * @var        string
     */
    const ACTIVITY_VALUE_TIME = 'time';
    /**
     * Tracks invalidations within the memory cache.
     *
     * @var        string
     */
    const MEMORY_CACHE_INVALIDATION = 'invalidation';
    /**
     * Tracks a hit to the memory cache.
     *
     * @var        string
     */
    const MEMORY_CACHE_HIT = 'hit';
    /**
     * Tracks a miss to the memory cache.
     *
     * @var        string
     */
    const MEMORY_CACHE_MISS = 'miss';
    /**
     * Tracks an addition to the memory cache.
     *
     * @var        string
     */
    const MEMORY_CACHE_ADD = 'add';
    /**
     * Tracks a removal from the memory cache.
     *
     * @var        string
     */
    const MEMORY_CACHE_REMOVE = 'remove';
    /**
     * Tracks memory cache execution time.
     *
     * @var        string
     */
    const MEMORY_CACHE_EXECUTION_TIME = 'executionTime';
    /**
     * Denotes an object loaded from process memory.
     *
     * @var        int
     */
    const MODEL_LOAD_MEMORY = 'memory';
    /**
     * Denotes an object loaded from the Object Cache.
     *
     * @var        int
     */
    const MODEL_LOAD_CACHE = 'cache';
    /**
     * Denotes an object loaded from the database.
     *
     * @var        int
     */
    const MODEL_LOAD_DATABASE = 'database';

    /**
     * Tracks the number of times a particular query is executed.
     *
     * @var        string
     */
    const QUERY_COUNT = 'count';
    /**
     * Tracks the stored procedure name or the SQL executed for a query.
     *
     * Only the first 40 characters of the query will be stored if not
     * a stored procedure.
     *
     * @var        string
     */
    const QUERY_SQL = 'sql';
    /**
     * Tracks the execution time of a query.
     *
     * @var        string
     */
    const QUERY_EXECUTION_TIME = 'executionTime';
    /**
     * Tracks the affected rows of a query.
     *
     * @var        string
     */
    const QUERY_AFFECTED_ROWS = 'affectedRows';
    /**
     * Tracks the result retrieval time of a query.
     *
     * @var        string
     */
    const QUERY_RETRIEVAL_TIME = 'retrievelTime';
    /**
     * Tracks the total processing time of a query.
     *
     * This is the time from execution of a query until the result is
     * freed from memory. This time could include additional processing time
     * by the function executing the query and should only be used as a
     * guide to the expense of the query as a whole.
     *
     * @var        string
     */
    const QUERY_PROCESSING_TIME = 'processingTime';
    /**
     * Template for an activity profile.
     *
     * @var        array
     */
    private static $activityTemplate = array(
        self::ACTIVITY_VALUE_ITERATIONS => 0,
        self::ACTIVITY_POINT_MODEL      => array(
            self::MODEL_LOAD_MEMORY   => 0,
            self::MODEL_LOAD_CACHE    => 0,
            self::MODEL_LOAD_DATABASE => 0,
        ),
        self::ACTIVITY_POINT_CACHE      => array(
            DCacheHandler::ACTION_HIT    => 0,
            DCacheHandler::ACTION_MISS   => 0,
            DCacheHandler::ACTION_REMOVE => 0,
            DCacheHandler::ACTION_ADD    => 0,
        ),
        self::ACTIVITY_POINT_START      => array(),
        self::ACTIVITY_POINT_END        => array(
            self::ACTIVITY_VALUE_TIME    => 0,
            self::ACTIVITY_VALUE_MEMORY  => 0,
            self::ACTIVITY_VALUE_QUERIES => 0,
        ),
    );
    /**
     * Information about profiled activities.
     *
     * @var        array
     */
    private $activities = array();
    /**
     * Pointers to activities that are currently being profiled.
     *
     * @var        array
     */
    private $activeActivities = array();
    /**
     * Shared memory cache activity tracking.
     *
     * @var        int
     */
    private $memoryCacheActivity;
    /**
     * Information about queries executed.
     *
     * @var        array
     */
    private $queries = array();
    /**
     * Total query execution time.
     *
     * @var        float
     */
    private $queryExecutionTime = 0;
    /**
     * Total query processing time.
     *
     * @var        float
     */
    private $queryProcessingTime = 0;

    /**
     * Creates a new profiler.
     *
     * @return    DFullProfiler
     */
    protected function __construct()
    {
        // Ensure the profiler report is generated
        // even if execution is halted.
        register_shutdown_function(array(self::class, 'shutdown'));
        $this->memoryCacheActivity = array(
            self::MEMORY_CACHE_INVALIDATION   => 0,
            self::MEMORY_CACHE_HIT            => 0,
            self::MEMORY_CACHE_MISS           => 0,
            self::MEMORY_CACHE_ADD            => 0,
            self::MEMORY_CACHE_REMOVE         => 0,
            self::MEMORY_CACHE_EXECUTION_TIME => 0,
        );
    }

    ///@cond INTERNAL
    /**
     * Returns object parameters.
     *
     * @param    string $name The name of the parameter to retrieve.
     *
     * @return    mixed
     * @deprecated
     */
    public function __get($name)
    {
        // Default for valid properties.
        if (property_exists($this, $name)) {
            $value = $this->$name;
        } else {
            $value = parent::__get($name);
        }

        return $value;
    }
    ///@endcond
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
    {
        $this->startActivity($name);
        // Reduce the iteration counter, this will have been incremented
        // by the start function unless this is a new activity.
        if ($this->activities[ $name ][ self::ACTIVITY_VALUE_ITERATIONS ] > 1) {
            --$this->activities[ $name ][ self::ACTIVITY_VALUE_ITERATIONS ];
        }
    }

    /**
     * Returns profile information about the specified activity.
     *
     * @param    string $name Name of the activity.
     *
     * @return    array
     */
    public function getActivityDetails($name)
    {
        return isset($this->activities[ $name ])
            ? $this->activities[ $name ]
            : array();
    }

    /**
     * Returns information about queries executed.
     *
     * @return    array
     */
    public function getQueryDetails()
    {
        return $this->queries;
    }

    /**
     * Returns the total execution time for all queries.
     *
     * @return    float
     */
    public function getQueryExecutionTime()
    {
        return $this->queryExecutionTime;
    }

    /**
     * Called when execution is halted as a PHP shutdown function to ensure
     * profiling information is generated.
     *
     * @return    void
     */
    public static function shutdown()
    {
        // Don't do anything if the profiler has already been stopped.
        $profiler = self::load();
        if (!$profiler->isRunning()) {
            return;
        }
        $profiler->stop();
        if (!DApplicationMode::isProductionMode()
            // Check that the router allows reporting.
            // @todo Do this in a better way, profiler shouldn't know about router!
            && (!DRouter::$router || DRouter::$router->profile())
        ) {
            $reportGenerator = DProfilerReportGenerator::decorate($profiler);
            $report = $reportGenerator->generateReport();
            DErrorHandler::$profiling[] =& $report;
        }
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
        parent::start($startTime);
        $this->startActivity(self::ACTIVITY_TOTAL, $startTime);
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
    public function startActivity($name, $startTime = null)
    {
        // Determine starting time, if not provided.
        if ($startTime === null) {
            $startTime = microtime();
        }
        // Determine starting memory usage.
        if ($name === self::ACTIVITY_TOTAL) {
            $memoryUsage = 0;
        } else {
            $memoryUsage = memory_get_usage();
        }
        // Set up the activity template, if this is a new activity.
        set_default($this->activities[ $name ], self::$activityTemplate);
        // Set starting information for the activity.
        $start =& $this->activities[ $name ][ self::ACTIVITY_POINT_START ];
        $start[ self::ACTIVITY_VALUE_TIME ] = array_sum(explode(' ', $startTime));
        $start[ self::ACTIVITY_VALUE_MEMORY ] = $memoryUsage;
        $start[ self::ACTIVITY_VALUE_QUERIES ] = DQuery::$queries;
        // Increment the iteration counter.
        ++$this->activities[ $name ][ self::ACTIVITY_VALUE_ITERATIONS ];
        // Set pointer to activity.
        $this->activeActivities[ $name ] =& $this->activities[ $name ];
    }

    /**
     * Stops the profiler.
     *
     * @return    void
     */
    public function stop()
    {
        parent::stop();
        // End activites that are still active.
        $wasActive = count($this->activeActivities);
        foreach (array_keys($this->activeActivities) as $name) {
            $this->stopActivity($name);
        }
        // Trigger the profiler report event to allow other functions
        // to log reports, if the profiler was active.
        if ($wasActive) {
            $event = new DOnProfilerReport();
            $this->notifyObservers($event);
        }
    }

    /**
     * Registers the end of a particular activity.
     *
     * @param    string $name The name of the activity.
     *
     * @return    void
     */
    public function stopActivity($name)
    {
        $start =& $this->activities[ $name ][ self::ACTIVITY_POINT_START ];
        if (isset($start[ self::ACTIVITY_VALUE_TIME ])) {
            $stopTime = array_sum(explode(' ', microtime()));
            $stopMemory = memory_get_usage();
            $stopQueries = DQuery::$queries;
            $end =& $this->activities[ $name ][ self::ACTIVITY_POINT_END ];
            $end[ self::ACTIVITY_VALUE_TIME ] += ($stopTime - $start[ self::ACTIVITY_VALUE_TIME ]);
            $end[ self::ACTIVITY_VALUE_MEMORY ] += ($stopMemory - $start[ self::ACTIVITY_VALUE_MEMORY ]);
            $this->activities[ $name ]['peakMemory'] = memory_get_peak_usage();
            $end[ self::ACTIVITY_VALUE_QUERIES ] += ($stopQueries - $start[ self::ACTIVITY_VALUE_QUERIES ]);
            // Reset the activity start information.
            $start = array();
        }
        // Clear pointer to activity.
        unset($this->activeActivities[ $name ]);
    }

    /**
     * Tracks cache activity.
     *
     * @param    int $action The caching action that occurred.
     *
     * @return    void
     */
    public function trackCacheAction($action)
    {
        foreach ($this->activeActivities as &$activity) {
            ++$activity[ self::ACTIVITY_POINT_CACHE ][ $action ];
        }
    }

    /**
     * Track a hit to the shared memory cache.
     *
     * @param    string $action           The action that occured, either
     *                                    {@link DFullProfiler::MEMORY_CACHE_HIT},
     *                                    {@link DFullProfiler::MEMORY_CACHE_MISS},
     *                                    {@link DFullProfiler::MEMORY_CACHE_ADD} or
     *                                    {@link DFullProfiler::MEMORY_CACHE_REMOVE},
     * @param    float  $executionTime    The number of seconds taken to retrieve
     *                                    data from the shared memory cache.
     *
     * @return    void
     */
    public function trackMemoryCacheAction($action, $executionTime = 0)
    {
        ++$this->memoryCacheActivity[ $action ];
        $this->memoryCacheActivity[ self::MEMORY_CACHE_EXECUTION_TIME ] += $executionTime;
    }

    /**
     * Tracks how objects are being loaded.
     *
     * @param    int $type The type of object load.
     *
     * @return    void
     */
    public function trackObjectLoad($type)
    {
        foreach ($this->activeActivities as &$activity) {
            ++$activity[ self::ACTIVITY_POINT_MODEL ][ $type ];
        }
    }

    /**
     * Tracks an aspect of query execution.
     *
     * @param    string $id       MD5 hash of the executed sql, or stored procedure name.
     * @param    string $aspect   The aspect of query execution to track. Must be one of
     *                            {@link DFullProfiler::QUERY_SQL},
     *                            {@link DFullProfiler::QUERY_EXECUTION_TIME},
     *                            {@link DFullProfiler::QUERY_AFFECTED_ROWS},
     *                            {@link DFullProfiler::QUERY_RETRIEVAL_TIME} or
     *                            {@link DFullProfiler::QUERY_PROCESSING_TIME}.
     * @param    mixed  $value    The value being tracked.
     *
     * @return    void
     */
    public function trackQuery($id, $aspect, $value)
    {
        if (!isset($this->queries[ $id ])) {
            $this->queries[ $id ] = array(
                self::QUERY_SQL             => implode('; ', $value),
                self::QUERY_COUNT           => 0,
                self::QUERY_EXECUTION_TIME  => 0,
                self::QUERY_AFFECTED_ROWS   => 0,
                self::QUERY_RETRIEVAL_TIME  => 0,
                self::QUERY_PROCESSING_TIME => 0,
            );
        }
        if ($aspect === self::QUERY_SQL) {
            $this->queries[ $id ][ self::QUERY_COUNT ]++;
        } else {
            $this->queries[ $id ][ $aspect ] += $value;
        }
        if ($aspect === self::QUERY_EXECUTION_TIME) {
            $this->queryExecutionTime += $value;
        }
        if ($aspect === self::QUERY_PROCESSING_TIME) {
            $this->queryProcessingTime += $value;
        }
    }
}
