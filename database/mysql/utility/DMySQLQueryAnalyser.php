<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\utility;

use app\decibel\database\DQuery;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\utility\DQueryAnalyser;

/**
 * MySQL query analyser.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DMySQLQueryAnalyser extends DQueryAnalyser
{
    /**
     *
     * @var        array
     */
    private static $goodJoinTypes = array(
        'system',
        'const',
        'eq_ref',
    );

    /**
     * Analyses the provided query and returns information about potential
     * execution issues.
     *
     * @param    DQuery $query The executed query.
     *
     * @return    array    List of potential problems, or <code>null</code>
     *                    if the query could not be analysed.
     * @see        http://dev.mysql.com/doc/refman/5.0/en/explain-output.html
     */
    public function analyse(DQuery $query)
    {
        $statement = $query->getStatement();
        if (!preg_match('/^\s*SELECT /i', $statement)) {
            $problems = null;
        } else {
            $resultCount = $query->getNumRows();
            $problems = array();
            $cartesianProduct = 1;
            $result = $this->query('EXPLAIN EXTENDED ' . $statement);
            while ($row = $this->getNextRow($result)) {
                $this->analyseRow($row, $cartesianProduct, $resultCount, $problems);
            }
            $this->analyseCartesianProduct($cartesianProduct, $resultCount, $problems);
        }

        return $problems;
    }

    /**
     *
     * @param    int   $cartesianProduct
     * @param    int   $resultCount
     * @param    array $problems
     *
     * @return    void
     */
    protected function analyseCartesianProduct($cartesianProduct,
                                               $resultCount, array &$problems)
    {
        if ($resultCount > 0) {
            $maxProduct = $resultCount * 100;
        } else {
            $maxProduct = 100;
        }
        if ($cartesianProduct > $maxProduct) {
            $problems[] = "Checked {$cartesianProduct} rows to return {$resultCount} results.";
        }
    }

    /**
     *
     * @param    array $row
     * @param    int   $cartesianProduct
     * @param    int   $resultCount
     * @param    array $problems
     *
     * @return    void
     */
    protected function analyseRow(array $row, &$cartesianProduct,
                                  $resultCount, array &$problems)
    {
        $table = $row['table'];
        $extra = explode('; ', $row['Extra']);
        $joinType = $row['type'];
        $joinRows = (int)$row['rows'];
        $cartesianProduct *= $joinRows;
        $this->analyseExtra($extra, $joinType, $table, $problems);
        $this->analyseIndex($row, $resultCount, $table, $problems);
        // Check join type.
        if ($joinType === 'ALL') {
            $problems[] = "Full table scan performed when processing table <code>{$table}</code>";
        }
    }

    /**
     *
     * @param    array  $extra
     * @param    string $joinType
     * @param    string $table
     * @param    array  $problems
     *
     * @return    void
     */
    protected function analyseExtra(array $extra, $joinType, $table, array &$problems)
    {
        if (in_array('Using filesort', $extra)) {
            $problems[] = "Filesort required when processing table <code>{$table}</code>";
        }
        if (in_array('Using temporary', $extra)) {
            $problems[] = "Temporary table required when processing table <code>{$table}</code>";
        }
        if (!in_array('Using where', $extra)
            && !in_array($joinType, self::$goodJoinTypes)
        ) {
            $problems[] = "No where clause applied when processing table <code>{$table}</code>";
        }
        if (!in_array('Using index', $extra)
            && !in_array($joinType, self::$goodJoinTypes)
        ) {
            $problems[] = "No index used when processing table <code>{$table}</code>";
        }
    }

    /**
     *
     * @param    array  $row
     * @param    int    $resultCount
     * @param    string $table
     * @param    array  $problems
     *
     * @return    void
     */
    protected function analyseIndex(array $row, $resultCount, $table, array &$problems)
    {
        $joinRows = (int)$row['rows'];
        $indexUsed = $row['key'];
        $indexLength = (int)$row['key_len'];
        // If no key was used, check that the number of returned rows
        // from this join is not greater than the total number of results.
        if ($indexUsed === null
            && $joinRows > $resultCount
        ) {
            $problems[] = "No index used when processing table <code>{$table}</code>";
        }
        // Check whether the index is much larger than the returned rows.
        if ($indexLength > ($joinRows * 100)) {
            $problems[] = "Checked {$indexLength} rows to return {$joinRows} results when processing table <code>{$table}</code>";
        }
    }

    /**
     * Attempts to explain the execution of the provided statement by the database.
     *
     * @param    DQuery $query The query to explain.
     *
     * @return    string    An explanation in the form of an HTML table,
     *                    or <code>null</code> if this is not possible.
     */
    public function explain(DQuery $query)
    {
        $result = $this->query('EXPLAIN ' . $query->getStatement());
        $row = $this->getNextRow($result);
        if (!$row) {
            return null;
        }
        // Use the keys from the first returned
        // row to build a header row.
        $head = '<thead><tr><th>'
            . implode('</th><th>', array_keys($row))
            . '</th></tr></thead>';
        $body = '';
        do {
            $body .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        } while ($row = $this->getNextRow($result));

        return "<table>{$head}{$body}</table>";
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DMySQL::class;
    }
}
