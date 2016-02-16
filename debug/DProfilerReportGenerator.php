<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\application\DClassManager;
use app\decibel\cache\DCacheHandler;
use app\decibel\decorator\DDecorator;
use app\decibel\file\DFile;
use app\decibel\http\request\DRequest;

/**
 * Contains functionality to generate a profiler report.
 *
 * @author    Timothy de Paris
 */
class DProfilerReportGenerator extends DDecorator
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DFullProfiler::class;
    }

    /**
     * Generates a profile report for the specified cache,
     * from the provided log.
     *
     * @param    string $cache    Qualified name of the cache.
     * @param    array  $log      Pointer to the cache log.
     *                            The original value will not be modified.
     *
     * @return    string
     */
    protected function generateCacheReport($cache, array &$log)
    {
        $i = 0;
        $cacheInfo = '';
        foreach ($log as $id => $actions) {
            $class = ($i % 2 === 0) ? 'even' : 'odd';
            $cacheInfo .= "<tr class=\"{$class}\">"
                . "<td>{$id}</td><td class=\"good\">{$actions[DCacheHandler::ACTION_HIT]}</td>"
                . "<td class=\"" . ($actions[ DCacheHandler::ACTION_MISS ] > 0 ? 'bad' : 'good') . "\">{$actions[DCacheHandler::ACTION_MISS]}</td>"
                . "<td class=\"good\">{$actions[DCacheHandler::ACTION_ADD]}</td>"
                . "<td class=\"" . ($actions[ DCacheHandler::ACTION_REMOVE ] > 0 ? 'ok' : 'good') . "\">{$actions[DCacheHandler::ACTION_REMOVE]}</td>"
                . '</tr>';
            $i++;
        }

        return "<h2>{$cache} Activity</h2>"
        . '<table>'
        . '<thead><tr><th style="width: 60%;">Key</th><th>Hit</th><th>Miss</th><th>Added</th><th>Removed</th></tr></thead><tbody>'
        . $cacheInfo
        . '</tbody></table>';
    }

    /**
     * Generates a profile report for caching functions.
     *
     * @return    string
     */
    protected function generateCachesReport()
    {
        $report = '';
        $caches = DClassManager::getClasses(DCacheHandler::class);
        foreach ($caches as $cache) {
            $log =& $cache::load()->getLog();
            if (count($log) > 0) {
                $report .= $this->generateCacheReport($cache, $log);
            }
        }

        return $report;
    }

    /**
     * Generates a profile report for queries.
     *
     * @return    string
     */
    protected function generateQueryReport()
    {
        $queryDetails = $this->getQueryDetails();
        if (count($queryDetails) === 0) {
            return '';
        }
        $queryInfo = '';
        foreach ($queryDetails as $query) {
            $sql = preg_replace('/[^%]%[^%]/', '%%', $query[ DFullProfiler::QUERY_SQL ]);
            $queryInfo .= sprintf("<tr><td>{$sql}</td><td class=\"good\">{$query[DFullProfiler::QUERY_COUNT]}</td><td class=\"good\">%0.4f sec</td><td class=\"good\">%0.4f sec</td><td class=\"good\">%0.4f sec</td><td class=\"good\">{$query[DFullProfiler::QUERY_AFFECTED_ROWS]}</td></tr>",
                                  $query[ DFullProfiler::QUERY_EXECUTION_TIME ],
                                  $query[ DFullProfiler::QUERY_RETRIEVAL_TIME ],
                                  $query[ DFullProfiler::QUERY_PROCESSING_TIME ]);
        }

        return '<h2>Database Query Information</h2>'
        . '<table id="queries">'
        . '<thead><tr><th style="width: 60%;">SQL / Stored Procedure</th><th>Iterations</th><th>Execution Time</th><th>Result Retrieval Time</th><th>Processing Time</th><th>Affected Rows</th></tr></thead><tbody>'
        . $queryInfo
        . '</tbody></table>';
    }

    /**
     * Returns a report on the time taken.
     *
     * @return    string
     */
    public function generateReport()
    {
        $activities = '';
        // General information.
        $totalExecution = $this->getActivityDetails(DFullProfiler::ACTIVITY_TOTAL);
        $report = '<h2>General Performance Details</h2>';
        $report .= '<table><tbody>';
        $report .= sprintf("\t<th width=\"180px\">Total Execution Time</th><td>%0.4f sec</td></tr>\n",
                           $totalExecution[ DFullProfiler::ACTIVITY_POINT_END ][ DFullProfiler::ACTIVITY_VALUE_TIME ]);
        $report .= sprintf("\t<th width=\"180px\">Query Execution Time</th><td>%0.4f sec (%0.1f%%)</td></tr>\n",
                           $this->queryExecutionTime,
            ($this->queryExecutionTime / $totalExecution[ DFullProfiler::ACTIVITY_POINT_END ][ DFullProfiler::ACTIVITY_VALUE_TIME ] * 100));
        $report .= sprintf("\t<th width=\"180px\">Query Processing Time</th><td>%0.4f sec (%0.1f%%)</td></tr>\n",
                           $this->queryProcessingTime,
            ($this->queryProcessingTime / $totalExecution[ DFullProfiler::ACTIVITY_POINT_END ][ DFullProfiler::ACTIVITY_VALUE_TIME ] * 100));
        $report .= sprintf("\t<tr><th width=\"180px\">Memory Cache Usage</th><td>%0.4f sec (%0.1f%%) [%d Hits, %d Missed, %d Added, %d Removed, %d Invalidations]</td></tr>\n",
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_EXECUTION_TIME ],
            ($this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_EXECUTION_TIME ] / $totalExecution[ DFullProfiler::ACTIVITY_POINT_END ][ DFullProfiler::ACTIVITY_VALUE_TIME ] * 100),
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_HIT ],
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_MISS ],
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_ADD ],
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_REMOVE ],
                           $this->memoryCacheActivity[ DFullProfiler::MEMORY_CACHE_INVALIDATION ]);
        $report .= "\t<tr><th width=\"180px\">Peak Memory Usage</th><td>" . DFile::bytesToString(memory_get_peak_usage(),
                                                                                                 5) . " (" . DFile::bytesToString(DFile::stringToBytes(ini_get('memory_limit'))) . " available)</td></tr>\n";
        $report .= '</tbody></table>';
        // Activity times.
        foreach ($this->activities as $name => $activity) {
            $end =& $activity[ DFullProfiler::ACTIVITY_POINT_END ];
            $length = $end[ DFullProfiler::ACTIVITY_VALUE_TIME ];
            $memory = DFile::bytesToString($end[ DFullProfiler::ACTIVITY_VALUE_MEMORY ], 2);
            $peakMemory = DFile::bytesToString($activity['peakMemory'], 2);
            $queries = $end[ DFullProfiler::ACTIVITY_VALUE_QUERIES ];
            $modelsMemory = $activity[ DFullProfiler::ACTIVITY_POINT_MODEL ][ DFullProfiler::MODEL_LOAD_MEMORY ];
            $modelsCache = $activity[ DFullProfiler::ACTIVITY_POINT_MODEL ][ DFullProfiler::MODEL_LOAD_CACHE ];
            $modelsDatabase = $activity[ DFullProfiler::ACTIVITY_POINT_MODEL ][ DFullProfiler::MODEL_LOAD_DATABASE ];
            $cacheHit = $activity[ DFullProfiler::ACTIVITY_POINT_CACHE ][ DCacheHandler::ACTION_HIT ];
            $cacheMiss = $activity[ DFullProfiler::ACTIVITY_POINT_CACHE ][ DCacheHandler::ACTION_MISS ];
            $iterations = $activity[ DFullProfiler::ACTIVITY_VALUE_ITERATIONS ];
            if ($iterations > 1) {
                $iterationLength = $length / $iterations;
                $iterationMemory = DFile::bytesToString($end[ DFullProfiler::ACTIVITY_VALUE_MEMORY ] / $iterations, 2);
                $iterationQueries = $queries / $iterations;
                $iterationObjectsMemory = $modelsMemory / $iterations;
                $iterationObjectsCache = $modelsCache / $iterations;
                $iterationObjectsDatabase = $modelsDatabase / $iterations;
                $iterationCacheHit = $cacheHit / $iterations;
                $iterationCacheMiss = $cacheMiss / $iterations;
                $activities .= sprintf("\t<tr><th>%s</th><td class=\"good\">%0.4f sec<br />(%0.4f sec)</td><td class=\"good\">%s<br />(%s)</td><td class=\"good\">%s</td><td class=\"good\">%d<br />(%d)</td><td class=\"good\">%d<br />(%d)</td><td class=\"good\">%d<br />(%d)</td><td class=\"%s\">%d<br />(%d)</td><td class=\"good\">%d<br />(%d)</td><td class=\"%s\">%d<br />(%d)</td><td class=\"good\">%d</td></tr>\n",
                                       $name, $length, $iterationLength, $memory, $iterationMemory, $peakMemory,
                                       $queries, $iterationQueries, $modelsMemory, $iterationObjectsMemory,
                                       $modelsCache, $iterationObjectsCache, $modelsDatabase == 0 ? 'good' : 'bad',
                                       $modelsDatabase, $iterationObjectsDatabase, $cacheHit, $iterationCacheHit,
                                       $cacheMiss == 0 ? 'good' : 'bad', $cacheMiss, $iterationCacheMiss, $iterations);
            } else {
                $activities .= sprintf("\t<tr><th>%s</th><td class=\"good\">%0.4f sec</td><td class=\"good\">%s</td><td class=\"good\">%s</td><td class=\"good\">%d</td><td class=\"good\">%d</td><td class=\"good\">%d</td><td class=\"%s\">%d</td><td class=\"good\">%d</td><td class=\"%s\">%d</td><td class=\"good\">%d</td></tr>\n",
                                       $name, $length, $memory, $peakMemory, $queries, $modelsMemory, $modelsCache,
                                       $modelsDatabase == 0 ? 'good' : 'bad', $modelsDatabase, $cacheHit,
                                       $cacheMiss == 0 ? 'good' : 'bad', $cacheMiss, $iterations);
            }
        }
        $report .= '<h2>Performance Break-down</h2>';
        $report .= sprintf('<table><thead><tr><th width="100px" rowspan="2">Activity</th><th rowspan="2">Execution Time</td><th rowspan="2">Memory Usage<br />Differential</th><th rowspan="2">Memory Usage<br />Peak</th><th rowspan="2">Queries</th><th colspan="3">Loaded Models</th><th colspan="2">Cache Activity</th><th rowspan="2">Iterations</th></tr><tr><th>Memory</th><th>Cache</th><th>Database</th><th>Hit</th><th>Miss</th></tr></thead><tbody>%s</tbody></table>',
                           $activities);
        // Query information.
        $report .= $this->generateQueryReport();
        // Cache information.
        $report .= $this->generateCachesReport() . '<br />';
        $url = DRequest::load()->getUrl();

        return "<h1 id=\"" . count(DErrorHandler::$profiling) . "\">Profiler ({$url})</h1>{$report}";
    }
}
