<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Executes a search that will return a list of object IDs.
 *
 * @author    Timothy de Paris
 */
class DIdsSearchExecuter extends DSearchExecuter
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
        $fields = array();
        $key = $this->key;
        if ($key) {
            $fields[ $key ] = new DFieldSelect($key);
        }
        if ($key !== 'id') {
            $fields['id'] = new DFieldSelect('id');
        }

        return $fields;
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
        return $this->ids;
    }
}
