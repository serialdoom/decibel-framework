<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a search that will return the included field values.
 *
 * @author    Timothy de Paris
 */
class DFieldsSearchExecuter extends DSearchExecuter
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DBaseModelSearch::class;
    }

    /**
     * Returns a list of fields to be included in the results.
     *
     * @return    array    List of {@link DFieldSelect} objects.
     */
    protected function getIncludedFields()
    {
        $fields = $this->__call('getIncludedFields', array());
        // If the key is not in the fields list, add it,
        // unless the fields list is empty, which means
        // all fields will be included anyway.
        if (count($fields) > 0) {
            $key = $this->key;
            if ($key && !isset($fields[ $key ])) {
                $fields[ $key ] = new DFieldSelect($key);
            }
        }

        return $fields;
    }

    /**
     * Processes the result of the executed search into the expected result format.
     *
     * @param    array $data
     *
     * @return    array
     */
    public function processResults(array $data)
    {
        $key = $this->key;
        $results = array();
        foreach ($data as &$row) {
            if ($key && isset($row[ $key ])) {
                $results[ $row[ $key ] ] = $row;
            } else {
                $results[] = $row;
            }
        }

        return $results;
    }
}
