<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\database\DDatabase;
use app\decibel\database\DQuery;
use app\decibel\model\database\DDatabaseMapper;

/**
 * Provides an abstracted interface to retrieve objects from a particular
 * model based on a set of criteria.
 *
 * @author        Majid Afzal
 */
class DLightModelSearch extends DBaseModelSearch
{
    /**
     * The database in which this search will be performed.
     *
     * Extending classes may set this to a foreign database to allow searching
     * over light models in a database other than the primary Decibel interface.
     *
     * @var        DDatabase
     */
    protected $database;

    /**
     * Creates a new DModelSearch.
     *
     * @param    string $qualifiedName    Qualified name of the model that will
     *                                    be searched.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        // Load a definition of the specified type.
        $this->qualifiedName = $qualifiedName;
        $this->definition = $qualifiedName::getDefinition();
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
        // Cache results by default.
        $this->enableCaching(true);
    }

    /**
     * Returns the SQL SELECT statement required to determine
     * the absolute count for this search.
     *
     * @return    string
     */
    protected function getAbsoluteCountSql()
    {
        return "COUNT(DISTINCT `{$this->tableName}`.`id`)";
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    protected function orderJoins()
    {
        // Order is not important for light models as there
        // are no internal joins. It is the responsibility of
        // individual criteria to add their joins in the correct
        // order to start with.
        return;
    }

    /**
     * Performs a query against the database.
     *
     * @param    string $sql        The SQL to execute.
     * @param    array  $parameters Parameters to use for the query.
     *
     * @return    DQuery
     */
    protected function query($sql, array $parameters = array())
    {
        $query = new DQuery($sql, $parameters, $this->database);
        if ($this->debug) {
            debug($query);
        }

        return $query;
    }
}
