<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Provides an abstracted interface to build sorting
 * for a {@link DBaseModelSearch}.
 *
 * @author        Nikolay Dimitrov
 */
abstract class DSortCriteria
{
    /**
     * Returns the ORDER BY clause and adds required JOINs to the search.
     *
     * @note
     * The order, DESC or ASC, should NOT be returned.
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL ORDER BY clause.
     */
    abstract public function getCriteria(DBaseModelSearch $search);
}
