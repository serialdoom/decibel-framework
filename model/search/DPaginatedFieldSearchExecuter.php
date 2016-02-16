<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a search that will return paginated values of a specified field.
 *
 * @author    Timothy de Paris
 */
class DPaginatedFieldSearchExecuter extends DFieldSearchExecuter
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
     * Processes the result of the executed search into the expected result format.
     *
     * @param    array $data
     *
     * @return    mixed
     */
    public function processResults(array $data)
    {
        $data = parent::processResults($data);
        $totalResults = $this->getAbsoluteCount();

        return $this->paginateResults($data, $totalResults);
    }
}
