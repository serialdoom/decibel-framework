<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a search that will return a single object instance.
 *
 * @author    Timothy de Paris
 */
class DObjectSearchExecuter extends DIdsSearchExecuter
{
    /**
     * The index of the object to return.
     *
     * @var        int
     */
    protected $index = 0;

    /**
     * Processes the result of the executed search into the expected result format.
     *
     * @param    array $data
     *
     * @return    mixed
     */
    public function processResults(array $data)
    {
        if (isset($this->ids[ $this->index ])) {
            $id = $this->ids[ $this->index ];
            $qualifiedName = $this->qualifiedName;
            $object = $qualifiedName::create((int)$id);
        } else {
            $object = null;
        }

        return $object;
    }

    /**
     * Sets the index of the object to return.
     *
     * @param    int $index
     *
     * @return    static
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }
}
