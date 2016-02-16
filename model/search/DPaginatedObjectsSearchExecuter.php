<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a model search that will return a paginated list of model instances.
 *
 * @author    Timothy de Paris
 */
class DPaginatedObjectsSearchExecuter extends DObjectsSearchExecuter
{
    use DPaginatedSearchExecuter;

    /**
     * Executes the search and returns the result.
     *
     * @return    array
     */
    public function execute()
    {
        $this->limitTo(
            $this->pageSize,
            ($this->pageNumber - 1) * $this->pageSize
        );

        return parent::execute();
    }

    /**
     * Returns the result of the executed search.
     *
     * @return    mixed
     */
    public function processResults(array $data)
    {
        $data = parent::getResults($data);
        $totalResults = $this->getAbsoluteCount();

        return $this->paginateResults($data, $totalResults);
    }
}
