<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\decorator\DDecorator;

/**
 * Provides the functionality required to execute a {@link DBaseModelSearch}
 * in order to return a specific type of result set.
 *
 * @author    Timothy de Paris
 */
abstract class DSearchExecuter extends DDecorator
{
    /**
     * Whether search results will be distinct.
     *
     * @var        bool
     */
    protected $distinct = true;

    /**
     * Result return type.
     *
     * @var        bool
     */
    protected $returnType = DBaseModelSearch::RETURN_SERIALIZED;

    /**
     * Executes the search and returns the result.
     *
     * @return    array
     */
    public function execute()
    {
        $data = $this->search(
            $this->getIncludedFields(),
            $this->returnType,
            $this->distinct
        );

        return $this->processResults($data);
    }

    /**
     * Returns a list of fields to be included in the results.
     *
     * @return    array    List of {@link DFieldSelect} objects.
     */
    abstract protected function getIncludedFields();

    /**
     * Processes the result of the executed search into the expected result format.
     *
     * @param    array $data
     *
     * @return    mixed
     */
    abstract protected function processResults(array $data);

    /**
     * Sets whether to ensure search results are distinct.
     *
     * @param    bool $distinct
     *
     * @return    static
     */
    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Sets the result return type.
     *
     * @param    string $returnType
     *
     * @return    static
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;

        return $this;
    }
}
