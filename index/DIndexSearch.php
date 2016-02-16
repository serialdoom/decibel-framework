<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\index;

use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\debug\DErrorHandler;
use app\decibel\index\DIndexRecord;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\search\DBaseModelSearch;

/**
 * Provides filtering and pagination capabilities to search on index records.
 *
 * @author        Timothy de Paris
 */
class DIndexSearch extends DBaseModelSearch
{
    /**
     * Creates a new DIndexSearch.
     *
     * @param    string $qualifiedName    Qualified name of the index record
     *                                    that will be searched.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        // Load a definition of the specified type.
        $this->qualifiedName = $qualifiedName;
        $this->definition = DIndexRecord::load($qualifiedName);
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
    }

    /**
     * Deletes all records matching the current search from the database.
     *
     * @return    int        The number of records deleted, or <code>null</code>
     *                    if an error occured during deletion.
     * @throws    DQueryExecutionException
     */
    public function delete()
    {
        // Prepare query components.
        $this->prepare();
        // Build query SQL.
        $this->sql = "DELETE FROM `{$this->tableName}`";
        // Add joins.
        if (count($this->joinSql)) {
            $this->sql .= implode(' ', $this->joinSql);
        }
        // Add where conditions.
        if (count($this->whereSql)) {
            $this->sql .= " WHERE " . implode(" AND ", $this->whereSql);
        }
        // Limit number of results.
        if ($this->limitTo) {
            $this->sql .= " LIMIT {$this->limitFrom}, {$this->limitTo}";
        }
        try {
            $query = $this->query($this->sql);
            $result = $query->getAffectedRows();
            // Catch any query execution exceptions to allow execution
            // to continue, however report the issue and halt in debug mode.
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::throwException($exception);
            $result = null;
        }

        return $result;
    }

    /**
     * Returns a list of model instances based on the specified criteria.
     *
     * This list can be paginated by providing the two optional parameters.
     *
     * @param    int $pageNumber      If provided, results will be paginated
     *                                starting from the specified page.
     * @param    int $pageSize        The number of results per page,
     *                                if pagination is being used.
     *
     * @return    array    Array of model instances. Keys will be non-associative
     *                    unless the {@link DBaseModelSearch::useKey()} method has
     *                    been called.
     */
    public function getObjects($pageNumber = null, $pageSize = 10)
    {
        $results = $this->getFields(
            DBaseModelSearch::RETURN_SERIALIZED,
            $pageNumber,
            $pageSize
        );
        $objects = array();
        $qualifiedName = $this->qualifiedName;
        foreach ($results as $data) {
            $objects[] = $qualifiedName::create($data);
        }

        return $objects;
    }

    /**
     * Returns the object returned at the specified index.
     *
     * If no parameters are specified, the first object will be returned.
     *
     * @param    int $index The index of the object to return
     *
     * @return    DIndexRecord    The specified object, or null if no object exists
     *                            at the specified index.
     */
    public function getObject($index = 0)
    {
        $results = $this->getFields();
        if (isset($results[ $index ])) {
            $qualifiedName = $this->qualifiedName;
            $object = $qualifiedName::create($results[ $index ]);
        } else {
            $object = null;
        }

        return $object;
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    protected function orderJoins()
    {
        // Order is not important for index records as there
        // are no internal joins. It is the responsibility of
        // individual criteria to add their joins in the correct
        // order to start with.
        return;
    }
}
