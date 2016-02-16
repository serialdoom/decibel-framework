<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Provides an abstracted interface to build grouping
 * for DBaseModelSearch queries.
 *
 * @author        Sajid Afzal
 */
abstract class DGroupCriteria
{
    /**
     * Returns the GROUP BY clause and adds required JOINs to the search.
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL GROUP BY clause.
     */
    abstract public function getCriteria(DBaseModelSearch $search);
}
