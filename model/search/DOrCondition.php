<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Provides interface to build SQL OR conditions
 *
 * @author    Nikolay Dimitrov
 */
class DOrCondition extends DSearchCondition
{
    /**
     * Conditions within this OR condition.
     *
     * @var        array    List of {@link DSearchCondition} objects.
     */
    private $subConditions;

    /**
     * Accepts variable number of arguments each of type DSearchCondition
     *
     * @return    static
     */
    public function __construct()
    {
        $subConditions = func_get_args();
        foreach ($subConditions as $condition) {
            $this->addCondition($condition);
        }
    }

    /**
     * Adds a condition to this search.
     *
     * @param    DSearchCondition $condition The condition to add.
     *
     * @return    static
     */
    public function addCondition(DSearchCondition $condition)
    {
        $this->subConditions[] = $condition;

        return $this;
    }

    /**
     * Return the WHERE condition and adds needed JOINs to the $search
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL WHERE clause condition
     */
    public function getCondition(DBaseModelSearch $search)
    {
        $conditions = array();
        foreach ($this->subConditions as $condition) {
            /* @var $condition DSearchCondition */
            $conditions[] = $condition->getCondition($search);
        }

        return $this->mergeConditions($conditions, DSearchCondition::OPERATOR_OR);
    }

    /**
     * Checks if any sub-conditions have been added to this condition.
     *
     * @return    bool
     */
    public function hasConditions()
    {
        return (bool)count($this->subConditions);
    }
}
