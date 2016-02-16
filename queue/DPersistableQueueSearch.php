<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\queue;

use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\search\DBaseModelSearch;

/**
 * Provides filtering and pagination capabilities to search
 * persistable queues.
 *
 * @author        Timothy de Paris
 */
class DPersistableQueueSearch extends DBaseModelSearch
{
    /**
     * Creates a new DPersistableQueueSearch.
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
        $this->definition = DPersistableQueue::load($qualifiedName);
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
    }

    /**
     * Orders joins for this search to ensure they are executed
     * in the correct sequence.
     *
     * @return    void
     */
    protected function orderJoins()
    {
        // Order is not important for queue records as there
        // are no internal joins. It is the responsibility of
        // individual criteria to add their joins in the correct
        // order to start with.
        return;
    }
}
