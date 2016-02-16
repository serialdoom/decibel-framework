<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\search;

use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\statement\DJoin;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\DModel;

/**
 * Provides an abstracted interface to retrieve objects from a particular
 * model based on a set of criteria.
 *
 * @author        Majid Afzal
 */
class DModelSearch extends DBaseModelSearch
{
    /**
     * Used when placing joins in the correct execution order for this search.
     *
     * @var        array
     */
    private $joinOrder;
    /**
     * Used when placing joins in the correct execution order for this search.
     *
     * @var        array
     */
    private $joinOrderOriginal;

    /**
     * Updates the model search following cloning.
     *
     * @return    void
     */
    public function __clone()
    {
        parent::__clone();
        $this->joinOrder = null;
        $this->joinOrderOriginal = null;
    }

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
        // Enable caching by default.
        $this->enableCaching(true);
        // Set default key.
        $this->key = 'id';
    }

    /**
     * Returns an array of fields that will be included in a serialized
     * version of this object.
     *
     * @return    array
     */
    public function __sleep()
    {
        $fields = parent::__sleep();
        $fields[] = 'conditions';

        return $fields;
    }

    /**
     * Applies any required default grouping for this search, if no group
     * criteria have been supplied.
     *
     * @note
     * {@link DModelSearch} searches will be grouped by ID if no specific
     * grouping criteria is applied, and there are one or more non-aggregated
     * fields included in the search.
     *
     * @return    void
     */
    protected function applyDefaultGroup()
    {
        /* @var $field DFieldSelect */
        $aggregateOnly = true;
        foreach ($this->fields as $field) {
            if ($field->getAggregateFunction() === self::AGGREGATE_NONE) {
                $aggregateOnly = false;
            }
        }
        if (empty($this->group)
            && !$aggregateOnly
        ) {
            $this->group[] = new DFieldGroup($this->definition->getField('id'));
        }
    }

    /**
     * Compare the SQL joins to ensure they are included in the query
     * in the correct order, based on the inheritance hierarchy of the model
     * being searched.
     *
     * @param    string $a The first join.
     * @param    string $b The second join.
     *
     * @return    int
     */
    protected function compareJoins($a, $b)
    {
        if (isset($this->joinOrder[ $a ])
            && !isset($this->joinOrder[ $b ])
        ) {
            $compare = -1;
        } else {
            if (!isset($this->joinOrder[ $a ])
                && !isset($this->joinOrder[ $b ])
            ) {
                // If neither form part of the model hierarchy, keep the original
                // order in which they were added.
                $originalA = $this->joinOrderOriginal[ $a ];
                $originalB = $this->joinOrderOriginal[ $b ];
                if ($originalA < $originalB) {
                    $compare = -1;
                } else {
                    $compare = 1;
                }
            } else {
                if (!isset($this->joinOrder[ $a ])
                    && isset($this->joinOrder[ $b ])
                ) {
                    $compare = 1;
                } else {
                    if ($this->joinOrder[ $a ] < $this->joinOrder[ $b ]) {
                        $compare = -1;
                    } else {
                        if ($this->joinOrder[ $a ] === $this->joinOrder[ $b ]) {
                            $compare = 0;
                        } else {
                            $compare = 1;
                        }
                    }
                }
            }
        }

        return $compare;
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
     * Prepares join order information used by the
     * {@link DBaseModelSearch::compareJoins()} method.
     *
     * @return    void
     */
    protected function prepareJoinOrder()
    {
        if ($this->joinOrder === null) {
            foreach ($this->definition->getInheritanceHierarchy() as $qualifiedName) {
                $this->joinOrder[] = DDatabaseMapper::getTableNameFor($qualifiedName);
            }
            $this->joinOrder = array_flip(array_reverse($this->joinOrder));
            $this->joinOrderOriginal = array_flip(array_keys($this->join));
        }
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    protected function orderJoins()
    {
        $this->prepareJoinOrder();
        uksort($this->join, array($this, 'compareJoins'));
    }

    /**
     * Prepares to execute a search.
     *
     * This involves converting all search options into SQL pieces ready
     * to be compiled into a query.
     *
     * @return    void
     */
    protected function prepare()
    {
        if ($this->prepared) {
            return;
        }
        // Always join to DModel table.
        if ($this->qualifiedName !== DModel::class) {
            $modelTable = DDatabaseMapper::getTableNameFor(DModel::class);
            $thisTable = $this->tableName;
            $this->addJoin(new DJoin(
                               $modelTable,
                               "`{$modelTable}`.`id`=`{$thisTable}`.`id`"
                           ));
        }
        parent::prepare();
    }

    /**
     * Performs the query and returns a processed result set.
     *
     * @param    array  $fields           Array containing field names to be
     *                                    included in the results, mapped to the
     *                                    aggregate function to be applied to
     *                                    each field.
     * @param    string $returnType       How field values will be returned. One of:
     *                                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     * @param    bool   $distinct         If <code>true</code>, only distinct
     *                                    field combinations will be returned.
     *
     * @return    array
     * @throws    DQueryExecutionException
     */
    protected function executeSearch(array $fields, $returnType, $distinct)
    {
        // Build the select SQL, allowing any joins
        // to be added before the search is prepared.
        $selectSql = $this->buildSelectSql($fields, $returnType);
        // Prepare query components for this search.
        $this->prepare();
        // Include any SELECT statements prodcued by prepare.
        $selectSql = array_merge(
            $selectSql,
            $this->selectSql
        );
        // Build query SQL.
        $sql = $this->buildSql($selectSql, $distinct);
        // Run query.
        try {
            $query = $this->query($sql);
            // Catch any query execution exceptions to allow execution
            // to continue, however report the issue and halt in debug mode.
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::throwException($exception);

            return array();
        }
        $data = array();
        while ($row = $query->getNextRow()) {
            // Convert values based on return type.
            foreach ($fields as $select) {
                $select->processRow($this, $row, $returnType);
            }
            // Store the data and key.
            $data[] = $row;
            $this->storeResultKey($row);
        }

        return $data;
    }
}
