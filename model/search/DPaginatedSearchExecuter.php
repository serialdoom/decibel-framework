<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\utility\DPagination;

/**
 * Adds pagination functionality to a {@link DSearchExecuter}.
 *
 * @author    Timothy de Paris
 */
trait DPaginatedSearchExecuter
{
    /**
     * The page number of results to return.
     *
     * @var        int
     */
    protected $pageNumber;
    /**
     * The number of results on each page.
     *
     * @var        int
     */
    protected $pageSize;

    /**
     * Adds the provided results to a {@link DPagination} object.
     *
     * @param    array $results      The page of results to include.
     * @param    int   $totalResults The total number of results available.
     *
     * @return    DPagination    The pagination object, or <code>null</code> if there
     *                        are no results.
     */
    protected function paginateResults(array $results = null, $totalResults = 0)
    {
        if ($totalResults === 0) {
            $pagination = null;
        } else {
            $pagination = new DPagination($totalResults, $this->pageNumber, $this->pageSize);
            $pagination->setFieldValue('pageContent', $results);
        }

        return $pagination;
    }

    /**
     * Sets the pagination criteria for the search.
     *
     * @param    int $pageNumber The page to return.
     * @param    int $pageSize   Number of items on each page.
     *
     * @return    static
     */
    public function setPagination($pageNumber, $pageSize)
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;

        return $this;
    }
}
