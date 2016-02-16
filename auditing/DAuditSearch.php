<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\auditing;

use app\decibel\auditing\DAuditRecord;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DInvalidSearchException;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\model\search\DFieldSort;

/**
 * Provides filtering and pagination capabilities to search on audit records.
 *
 * @author        Nikolay Dimitrov
 */
class DAuditSearch extends DBaseModelSearch
{
    /**
     * Creates a new DAuditSearch.
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
        $this->definition = DAuditRecord::load($qualifiedName);
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
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
     * @throws  DInvalidSearchException This method cannot be used for audit records.
     */
    public function getObjects($pageNumber = null, $pageSize = 10)
    {
        throw new DInvalidSearchException('Cannot create an instance of a DAuditRecord, please use DAuditSearch::getFields() instead');
    }

    /**
     * Returns the object returned at the specified index.
     *
     * If no parameters are specified, the first object will be returned.
     *
     * @param    int $index The index of the object to return
     *
     * @return    DModel    The specified object, or null if no object exists
     *                    at the specified index.
     * @throws  DInvalidSearchException This method cannot be used for audit records.
     */
    public function getObject($index = 0)
    {
        throw new DInvalidSearchException('Cannot create an instance of a DAuditRecord, please use DAuditSearch::getFields() instead');
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    protected function orderJoins()
    {
        // Order is not important for audit records as there
        // are no internal joins. It is the responsibility of
        // individual criteria to add their joins in the correct
        // order to start with.
        return;
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
        // Default sort to ID.
        if (count($this->sort) == 0) {
            $this->sort[] = new DFieldSort($this->definition->getField(DAuditRecord::FIELD_ID));
            $this->sortOrder[] = DAuditSearch::ORDER_DESC;
        }
        parent::prepare();
    }
}
