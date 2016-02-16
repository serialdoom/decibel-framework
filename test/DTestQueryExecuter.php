<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

use app\decibel\database\DQueryExecuter;
use app\decibel\database\debug\DQueryExecutionException;

/**
 * Undertakes execution of {@link DQueryTester} queries.
 *
 * @author    Timothy de Paris
 */
class DTestQueryExecuter extends DQueryExecuter
{
    /**
     * The query testing information.
     *
     * @var        DQueryTester
     */
    protected $tester;

    /**
     * Creates a new {@link DQueryExecuter}.
     *
     * @param    DQueryTester $tester The query testing information.
     * @param    array        $params Parameters passed to the query.
     *
     * @return    static
     * @throws    DQueryExecutionException    If execution fails.
     */
    public function __construct(DQueryTester $tester, array &$params)
    {
        parent::__construct();
        $this->tester = $tester;
        $this->returnedRows = $tester->getNumRows();
        // Check required parameters.
        $expectedParameters = $tester->getExpectedParameters();
        if ($expectedParameters !== null) {
            ksort($params);
            ksort($expectedParameters);
            if (serialize($params) !== serialize($expectedParameters)) {
                throw new DInvalidQueryParametersException($params, $expectedParameters);
            }
        }
        $this->handleError($tester->getError());
        $this->result = true;
    }

    /**
     * Frees memory and resources associated with the query.
     *
     * @return    void
     */
    public function free()
    {
        // Nothing to do.
    }

    /**
     * Returns the last error to occur in the database.
     *
     * @return    DDatabaseException    An exception object describing the error,
     *                                or <code>null</code> if no error occurred.
     */
    public function getError()
    {
        return $this->tester->getError();
    }

    /**
     * Return the next row of results from the query.
     *
     * @return    array    The next row, or <code>null</code> if there are no more rows.
     */
    public function getNextRow()
    {
        return $this->tester->getNextRow();
    }

    /**
     * Returns the results of the query as a multi-dimensional array.
     *
     * @return    array    A pointer to the query results.
     */
    public function &getResults()
    {
        return $this->tester->getResults();
    }
}
