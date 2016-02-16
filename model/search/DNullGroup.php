<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Allows removal of grouping from a model search.
 *
 * An instance of this class can be passed to
 * {@link app::decibel::model::search::DBaseModelSearch::groupBy()}
 * to define the grouping of returned search results.
 *
 * @author        Sajid Afzal
 */
class DNullGroup extends DGroupCriteria
{
    /**
     * Returns the GROUP BY clause and adds required JOINs to the search.
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL GROUP BY clause.
     */
    public function getCriteria(DBaseModelSearch $search)
    {
        return null;
    }
}
