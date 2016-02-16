<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\field\DFieldSearch;

/**
 * Provides filtering to ignore one or more models from a search.
 *
 * @author    Timothy de Paris
 */
class DIgnoreCondition extends DFieldCondition
{
    /**
     * Sets the objects to be ignored from the search.
     *
     * @param    array $ignore Objects or IDs to ignore.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     */
    public function __construct(array $ignore)
    {
        // Validate and normalise each of the ignored objects.
        foreach ($ignore as &$value) {
            $this->normalise($value);
        }
        parent::__construct('id', $ignore, DFieldSearch::OPERATOR_NOT_IN);
    }

    /**
     * Normalises an ignore value.
     *
     * @param    mixed $value
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If a value cannot be normalised.
     */
    protected function normalise(&$value)
    {
        if (is_object($value)) {
            $value = $value->getId();
        }
        if (!is_numeric($value)) {
            throw new DInvalidParameterValueException(
                'ignore',
                array(__CLASS__, __FUNCTION__),
                'array of objects or IDs'
            );
        }
    }
}
