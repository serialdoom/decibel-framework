<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\database;

use app\decibel\database\DDatabase;
use app\decibel\database\debug\DInvalidColumnException;
use app\decibel\database\debug\DInvalidParameterValueException;
use app\decibel\database\debug\DInvalidRowException;
use app\decibel\database\debug\DPacketSizeException;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\utility\DQueryAnalyser;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DProfiler;
use app\decibel\test\DQueryTester;
use app\decibel\test\DTestQueryExecuter;
use app\decibel\utility\DBaseClass;

/**
 * Handles the retrieval of data from database tables.
 *
 * @section        why Why Would I Use It?
 *
 * The DQuery object can be used to run queries directly against the %Decibel
 * database, or an external database.
 *
 * @note
 * It is not recommended to run queries directly against the %Decibel database
 * to query or manipulate model data as this could result in corupted data.
 * See the @ref model_orm Developer Guide for information about using
 * %Decibel's inbuilt ORM functionality before reverting to direct
 * database queries.
 *
 * @section        how How Do I Use It?
 *
 * See the @ref database_querying Developer Guide for details about using
 * the DQuery class.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database
 */
class DQuery implements DDebuggable
{
    use DBaseClass;

    /**
     * Keeps track of the number of queries run for statistical information.
     *
     * @var        int
     */
    public static $queries = 0;
    /**
     * The database controller used by this query.
     *
     * @var        DDatabase
     */
    protected $database;
    /**
     * The {@link DQueryExecuter} instance responsible for executing this query.
     *
     * @var        DQueryExecuter
     */
    protected $executer;
    /**
     * The first row of the result set.
     *
     * @var        array
     */
    protected $firstRow;
    /**
     * Internal ID of the query, used for profiling purposes.
     *
     * @note
     * When profiling is disabled this parameter will be <code>null</code>.
     *
     * @var        string
     */
    protected $queryId;
    /**
     * The statement that this query executed, for debugging purposes.
     *
     * @var        array
     */
    protected $statement;

    /**
     * Creates and executes a query.
     *
     * @param    array     $statement     The statement or name of the stored procedure to execute.
     * @param    array     $params        Associative array containing query parameters.
     * @param    DDatabase $database      The database controller that will execute the query.
     *                                    If not provided, the application database will be used.
     *
     * @return    static
     * @throws    DQueryExecutionException        If the query causes an error.
     * @throws    DInvalidParameterValueException    If an invalid parameter value is provided.
     * @throws    DPacketSizeException            If the specified query exceeds
     *                                            the server's maximum packet size.
     */
    public function __construct($statement, array $params = array(), DDatabase $database = null)
    {
        // Normalise provided statement as a list of statements.
        $statement = (array)$statement;
        // Generate a unique ID for the query if profiling.
        $flatStement = implode('', $statement);
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $this->queryId = md5($flatStement);
        }
        // Is this query being tested?
        if (DQueryTester::hasTestQuery($flatStement)) {
            $this->executer = new DTestQueryExecuter(
                DQueryTester::getTestQuery($flatStement),
                $params
            );
            // Otherwise actually execute it.
        } else {
            $this->database = $this->selectDatabase($database);
            $this->statement = $this->processStatement($statement, $params);
            $this->executer = new DDefaultQueryExecuter($this->database, $this->statement);
        }
        // Track the number of executed queries.
        self::$queries += count($this->statement);
    }

    /**
     * Free memory associated with the query.
     *
     * @return    void
     */
    public function __destruct()
    {
        // iff $this->executer is defined, free()
        if ($this->executer !== null) {
            $this->executer->free();
        }
    }

    ///@cond INTERNAL
    /**
     * Replaces and escapes values for using within statements.
     *
     * @param    mixed     $value         The value to escape.
     * @param    bool      $enclose       Whether to enclose the escaped value
     *                                    with parenthesis for arrays or quotes
     *                                    for strings. Numeric values will not
     *                                    be enclosed with quotes.
     * @param    DDatabase $database      The database connection to escape
     *                                    the value for.
     *
     * @return    string
     * @throws    DInvalidParameterValueException    If an invalid value is provided.
     * @deprecated    In favour of {@link DStatementPreparer::escapeValue}
     */
    public static function escapeValue($value, $enclose = false, DDatabase $database = null)
    {
        if ($database === null) {
            $database = DDatabase::getDatabase();
        }

        return DStatementPreparer::adapt($database)
                                 ->escapeValue($value, $enclose);
    }
    ///@endcond
    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        if (count($this->statement) === 1) {
            $analyser = DQueryAnalyser::adapt($this->database);
            $explain = $analyser->explain($this);
            $problems = $analyser->analyse($this);
        } else {
            $explain = null;
            $problems = null;
        }

        return array(
            '*statement|statement' => $this->getStatement(),
            '*explain'             => $explain,
            'problems'             => $problems,
            'success'              => $this->executer->isSuccessful(),
            'executer'             => $this->executer,
            'database'             => $this->database,
        );
    }

    /**
     * Returns the given column from the first row of this query's
     * result set.
     *
     * @param    string $name The name of the column to retrieve.
     *
     * @throws    DInvalidRowException    If the result set contains no data.
     * @throws    DInvalidColumnException    If the column does not exist.
     * @return    mixed
     */
    public function get($name)
    {
        if ($this->firstRow === null) {
            $this->firstRow = $this->getNextRow();
            // Check that there's a row.
            if (!$this->firstRow) {
                throw new DInvalidRowException(0);
            }
        }
        // Check that the column exists.
        if (!array_key_exists($name, $this->firstRow)) {
            throw new DInvalidColumnException($name);
        }

        return $this->firstRow[ $name ];
    }

    /**
     * Returns the number of rows affected by the query.
     *
     * @return    int
     */
    public function getAffectedRows()
    {
        return $this->executer->getAffectedRows();
    }

    /**
     * Returns the ID generated for an AUTO_INCREMENT column by the
     * previous INSERT query.
     *
     * @return    int
     */
    public function getInsertId()
    {
        return $this->executer->getInsertId();
    }

    /**
     * Return the number of rows in this queries result.
     *
     * @note
     * If more than one query is executed, the number of results returned
     * by the final query will returned by this function.
     *
     * @return    int
     */
    public function getNumRows()
    {
        return $this->executer->getReturnedRows();
    }

    /**
     * Return the next row of results from the query.
     *
     * @return    array    The next row, or <code>null</code> if there are no more rows.
     */
    public function getNextRow()
    {
        return $this->executer->getNextRow();
    }

    /**
     * Returns the results of the query as a multi-dimensional array.
     *
     * @return    array    The query results.
     */
    public function &getResults()
    {
        return $this->executer->getResults();
    }

    /**
     * Returns the last error to occur in the database.
     *
     * @return    DDatabaseException    An exception object describing the error,
     *                                or <code>null</code> if no error occurred.
     */
    public function getError()
    {
        return $this->executer->getError();
    }

    /**
     * Returns the executed statement.
     *
     * @return    string
     */
    public function getStatement()
    {
        return implode('; ', $this->statement);
    }

    /**
     * Determines if the query executed successfully.
     *
     * @return    bool
     */
    public function isSuccessful()
    {
        return $this->executer->isSuccessful();
    }

    /**
     * Processes the provided statement so that it is ready for execution.
     *
     * @param    array $statement List of statements or stored procedure names.
     * @param    array $params    List of parameters expected by the query.
     *
     * @return    array
     */
    protected function processStatement(array $statement, array $params = array())
    {
        // Substitute parameters.
        $converted = array();
        foreach ($statement as $query) {
            $storedProcedure = DStoredProcedure::get($query);
            if ($storedProcedure === null) {
                $converted[] = $query;
            } else {
                $converted = array_merge($converted, $storedProcedure);
            }
        }
        $statementPreparer = DStatementPreparer::adapt($this->database);
        foreach ($converted as &$query) {
            $query = $statementPreparer->prepare($query, $params);
        }

        return $converted;
    }

    /**
     * Sets the database that this query will be executed against.
     *
     * @param    DDatabase $database      The database, or <code>null</code>
     *                                    if the application database should
     *                                    be queried.
     *
     * @return    DDatabase
     */
    protected function selectDatabase(DDatabase $database = null)
    {
        // Obtain database interface.
        if ($database === null) {
            $database = DDatabase::getDatabase();
        } else {
            // If a database was provided, ensure it is connected.
            if (!$database->connected()) {
                $database->connect();
            }
        }

        return $database;
    }
}
