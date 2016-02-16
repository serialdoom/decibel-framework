<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\debug\DFullProfiler;
use app\decibel\debug\DProfiler;

/**
 * Undertakes execution of queries.
 *
 * @author    Timothy de Paris
 */
class DDefaultQueryExecuter extends DQueryExecuter
{
    /**
     * The number of milliseconds this query took to execute.
     *
     * @var        float
     */
    protected $executionTime;
    /**
     * The microtime at which this query started execution.
     *
     * Used to calculate query profiling information.
     *
     * @var        float
     */
    protected $startTime;

    /**
     * Creates a new {@link DQueryExecuter}.
     *
     * @param    DDatabase $database  Database against which to execute the queries.
     * @param    array     $statement The statements to be executed.
     *
     * @return    static
     * @throws    DQueryExecutionException    If execution fails.
     */
    public function __construct(DDatabase $database, array $statement)
    {
        parent::__construct();
        $this->database = $database;
        // Store profiling information.
        $this->startTime = microtime(true);
        // Query database and return results.
        $this->executeQueries($database, $statement);
        // Calculate profiling information.
        $this->executionTime = (microtime(true) - $this->startTime);
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $profiler = DFullProfiler::load();
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_SQL, $statement);
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_EXECUTION_TIME, $this->executionTime);
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_AFFECTED_ROWS, $this->affectedRows);
        }
    }

    /**
     * Executes the query(s).
     *
     * @param    DDatabase $database Database against which to execute the queries.
     * @param    array     $queries  List of statements.
     *
     * @return    void
     * @throws    DQueryExecutionException        If the query causes an error.
     */
    protected function executeQueries(DDatabase $database, array $queries)
    {
        // Query database and return results.
        foreach ($queries as $query) {
            $this->executeQuery($database, $query);
            // Report any errors thrown by the database for this query.
            $this->handleError($this->getError(), $query);
        }
    }

    /**
     * Execute a single query against the database.
     *
     * @param    DDatabase $database Database against which to execute the query.
     * @param    string    $query    The query to execute.
     *
     * @return    void
     */
    protected function executeQuery(DDatabase $database, $query)
    {
        $this->result = $database->query($query);
        // Store returned row count.
        $this->returnedRows = $database->getNumRows($this->result);
        // Increment affected row count.
        if ($this->result) {
            $this->affectedRows += $database->getAffectedRows();
        }
        // Determine insert id.
        $insertId = $database->getInsertId();
        if (!empty($insertId)) {
            $this->insertId = (int)$insertId;
        }
    }

    /**
     * Frees memory and resources associated with the query.
     *
     * @return    void
     */
    public function free()
    {
        $this->database->freeResult($this->result);
        $this->result = null;
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $profiler = DFullProfiler::load();
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_PROCESSING_TIME,
                (microtime(true) - $this->startTime));
        }
    }

    /**
     * Returns the last error to occur in the database.
     *
     * @return    DDatabaseException    An exception object describing the error,
     *                                or <code>null</code> if no error occurred.
     */
    public function getError()
    {
        return $this->database->getError();
    }

    /**
     * Return the next row of results from the query.
     *
     * @return    array    The next row, or <code>null</code> if there are no more rows.
     */
    public function getNextRow()
    {
        if (!$this->result) {
            return null;
        }
        $retrievalTime = microtime(true);
        $row = $this->database->getNextRow($this->result);
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $profiler = DFullProfiler::load();
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_RETRIEVAL_TIME,
                                  microtime(true) - $retrievalTime);
        }
        // Automatically free the query if this was the last row to be returned.
        if ($row === false) {
            $this->free();
        }

        return $row;
    }

    /**
     * Returns the results of the query as a multi-dimensional array.
     *
     * @return    array    A pointer to the query results.
     */
    public function &getResults()
    {
        $retrievalTime = microtime(true);
        $resultArray = array();
        while ($row = $this->database->getNextRow($this->result)) {
            $resultArray[] = $row;
        }
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $profiler = DFullProfiler::load();
            $profiler->trackQuery($this->queryId, DFullProfiler::QUERY_RETRIEVAL_TIME,
                                  microtime(true) - $retrievalTime);
        }
        // Free result memory.
        $this->free();

        return $resultArray;
    }
}
