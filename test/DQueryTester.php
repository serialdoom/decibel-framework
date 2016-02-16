<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

use app\decibel\database\DQuery;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\database\debug\DQueryExecutionException;

/**
 * Provides a mechanism for testing of methods that query the database
 * without needing to manipulate data within the database.
 *
 * When a {@link DQueryTester} is created, it automatically registers
 * itself with the {@link app::decibel::database::DQuery DQuery} class,
 * ensuring that whenever the query it has been created for is executed,
 * the returns {@link app::decibel::database::DQuery DQuery} object
 * is populated with the result data specified by the tester.
 *
 * @author    Timothy de Paris
 */
class DQueryTester
{
    /**
     * The query SQL or stored procedure name to be tested.
     *
     * @var        string
     */
    protected $query;
    /**
     * The result that should be returned for the query.
     *
     * @var        array
     */
    protected $results = array();
    /**
     * The exception to be thrown while testing this query, if any.
     *
     * @var        DDatabaseException
     */
    protected $exception;
    /**
     * The parameters that will be expected by this query.
     *
     * @var        array
     */
    protected $expectedParameters;
    /**
     * The next row of results to be returned.
     *
     * @var        int
     */
    protected $index = 0;
    /**
     * Registers {@link DQueryTest} objects.
     *
     * @var        array
     */
    private static $testQueries = array();

    /**
     * Creates a new {@link DQueryTester} object.
     *
     * @param    string $query   The query SQL or stored procedure name to be tested.
     * @param    array  $columns Columns that should be returned in the result set.
     * @param    array  $results Results that should be returned for the query.
     *
     * @return    DQueryTester
     * @throws    DInvalidResultsException    If the provided results are not
     *                                        valid for the provided columns.
     */
    protected function __construct($query, array $columns = array(),
                                   array $results = array())
    {
        $this->query = $query;
        // Turns results in associative arrays.
        $columnCount = count($columns);
        foreach ($results as $row) {
            // Validate the row.
            if (!is_array($row)
                || count($row) !== $columnCount
            ) {
                throw new DInvalidResultsException($columnCount);
            }
            // Turn it into an associative array and store.
            $this->results[] = array_combine($columns, $row);
        }
        // Register the tester.
        self::$testQueries[ $this->query ] = $this;
    }

    /**
     * Creates a new {@link DQueryTester} object.
     *
     * @section example Example
     *
     * @code
     * // Set up query result data.
     * $columns = array('column1', 'column2');
     * $results = array(
     *    array(1, 2),
     *    array(3, 4),
     * );
     *
     * // Create the query tester.
     * $tester = DQueryTester::create(
     *    'SELECT * FROM `mytable`;',
     *    $columns,
     *    $results
     * );
     *
     * // Execute the query.
     * $query = new DQuery('SELECT * FROM `mytable`;');
     * echo $query->getNumRows();
     * while ($row = $query->getNextRow()) {
     *   echo $row['column1'];
     * }
     * @endcode
     *
     * This will output:
     *
     * @code
     * 213
     * @endcode
     *
     * @param    string $query   The query SQL or stored procedure name to be tested.
     * @param    array  $columns Columns that should be returned in the result set.
     * @param    array  $results Results that should be returned for the query.
     *
     * @return    static
     * @throws    DInvalidResultsException    If the provided results are not
     *                                        valid for the provided columns.
     */
    final public static function create($query, array $columns = array(),
                                        array $results = array())
    {
        return new static($query, $columns, $results);
    }

    /**
     * Allows the tester to validate the parameters provided to the query.
     *
     * This method accepts a variable-length argument list. The first set
     * of parameters will be expected on the first query execution, the second
     * will be expected the second time the query is executed, and so on.
     *
     * If called with no arugments, the query will not validate it's parameters.
     * This is the default state of the {@link DQueryTester} object.
     *
     * @note
     * The {@link app::decibel::database::DQuery DQuery} object will throw a
     * {@link app::decibel::test::DInvalidQueryParametersException DInvalidQueryParametersException}
     * if the provided parameters do not match the expected parameters.
     *
     * @section example Example
     *
     * @code
     * // Set up expected parameter data.
     * $execution1 = array(
     *    'param1'    => 'value1',
     *    'param2'    => 'value2',
     * );
     * $execution2 = array(
     *    'param1'    => 'value3',
     *    'param2'    => 'value4',
     * );
     *
     * // Create the query tester.
     * $tester = DQueryTester::create(
     *    'SELECT * FROM `mytable`;',
     *    $columns,
     *    $results
     * );
     * $tester->expectParameters($execution1, $execution2);
     *
     * // Execute the first query.
     * new DQuery('SELECT * FROM `mytable`;', array(
     *    'param1'    => 'value1',
     *    'param2'    => 'value2',
     * ));
     *
     * // Execute the first query.
     * // This will throw a DInvalidQueryParametersException
     * // as the provided parameters do not match the expected
     * // parameters for the second execution of this query.
     * new DQuery('SELECT * FROM `mytable`;', array(
     *    'param1'    => 'valueX',
     *    'paramY'    => 'value4',
     * ));
     * @endcode
     *
     * @return    static
     */
    public function expectParameters()
    {
        $arguments = func_get_args();
        if (count($arguments) === 0) {
            $this->expectedParameters = null;
        } else {
            $this->expectedParameters = $arguments;
        }

        return $this;
    }

    /**
     * Returns the exception to be thrown while testing this query, if any.
     *
     * @return    DDatabaseException    The exception, or <code>null</code>
     *                                if no exception is to be thrown while
     *                                testing this query.
     */
    public function getError()
    {
        return $this->exception;
    }

    /**
     * Returns the expected parameters for the current execution.
     *
     * @return    array    The expected parameters, or <code>null</code>
     *                    if no check on parameters will be performed.
     */
    public function getExpectedParameters()
    {
        if ($this->expectedParameters === null) {
            return null;
        }

        return array_shift($this->expectedParameters);
    }

    /**
     * Returns the query SQL or stored procedure name to be tested.
     *
     * @return    string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns the next row of results.
     *
     * @return    array    The next row of results, or <code>null</code>
     *                    if there are no more results.
     */
    public function getNextRow()
    {
        if (isset($this->results[ $this->index ])) {
            $result = $this->results[ $this->index ];
            ++$this->index;
            // No more results.
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Returns the number of rows in the result set.
     *
     * @return    int
     */
    public function getNumRows()
    {
        return count($this->results);
    }

    /**
     * Returns the result set.
     *
     * @return    array
     */
    public function &getResults()
    {
        return $this->results;
    }

    /**
     * Returns the {@link DQueryTester} registered for the specified SQL statement.
     *
     * @param    string $sql
     *
     * @return    DQueryTester    The query tester, or <code>null</code> if none has been registered
     *                            got the provided SQL statement.
     */
    public static function getTestQuery($sql)
    {
        if (isset(self::$testQueries[ $sql ])) {
            $tester = self::$testQueries[ $sql ];
        } else {
            $tester = null;
        }

        return $tester;
    }

    /**
     * Determines if a {@link DQueryTester} has been registered for the specified SQL statement.
     *
     * @param    string $sql
     *
     * @return    bool
     */
    public static function hasTestQuery($sql)
    {
        return isset(self::$testQueries[ $sql ]);
    }

    /**
     * Resets the internal row pointer to allow results to be retrieved again.
     *
     * @return    void
     */
    public function reset()
    {
        $this->index = 0;
    }

    /**
     * Instructs the {@link app::decibel::database::DQuery DQuery} class
     * to throw an exception when executing this query tester.
     *
     * @param    DDatabaseException $exception    The exception to throw.
     *                                            If not provided, a randomised
     *                                            exception will be thrown.
     *
     * @return    static
     */
    public function setErrorState(DDatabaseException $exception = null)
    {
        if ($exception === null) {
            $this->exception = new DQueryExecutionException(
                1000,
                'An error occurred while executing the query.'
            );
        } else {
            $this->exception = $exception;
        }

        return $this;
    }
}
