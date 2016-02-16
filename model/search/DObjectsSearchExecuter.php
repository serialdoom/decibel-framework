<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a search that will return a list of object instances.
 *
 * @author    Timothy de Paris
 */
class DObjectsSearchExecuter extends DIdsSearchExecuter
{
    /**
     * Processes the result of the executed search into the expected result format.
     *
     * @param    array $data
     *
     * @return    mixed
     */
    public function processResults(array $data)
    {
        $qualifiedName = $this->qualifiedName;
        $results = array();
        foreach ($this->ids as $position => $id) {
            if ($this->key) {
                $key = $this->keys[ $position ];
            } else {
                $key = $position;
            }
            $results[ $key ] = $qualifiedName::create((int)$id);
        }
    }
}
