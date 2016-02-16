<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\debug\DInvalidParameterValueException;

/**
 * Executes a search that will return the values of a specified field.
 *
 * @author    Timothy de Paris
 */
class DFieldSearchExecuter extends DSearchExecuter
{
    /**
     * The index of the ID to return.
     *
     * @var        int
     */
    protected $fieldName;

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
        // If the requested field has already been included,
        // use that definition, otherwise create a new one.
        $fields = $this->getDecorated()->getIncludedFields();
        if (isset($fields[ $this->fieldName ])) {
            $fields = array($this->fieldName => $fields[ $this->fieldName ]);
        } else {
            $fields = array($this->fieldName => new DFieldSelect($this->fieldName));
        }
        // Determine key to use.
        $key = $this->key;
        if ($key) {
            $fields[ $key ] = new DFieldSelect($key);
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
            $rowData = $row[ $this->fieldName ];
            if ($key && isset($row[ $key ])) {
                $results[ $row[ $key ] ] = $rowData;
            } else {
                $results[] = $rowData;
            }
        }

        return $results;
    }

    /**
     * Sets the name of the field to return.
     *
     * @param    string $fieldName
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the specified field does not exist
     *                                            for the searched model.
     */
    public function setFieldName($fieldName)
    {
        // Validate requested field.
        $definitionFields = $this->getDefinition()->getFields();
        if (!isset($definitionFields[ $fieldName ])) {
            throw new DInvalidParameterValueException(
                'fieldName',
                array(__CLASS__, __FUNCTION__),
                'valid field name'
            );
        }
        $this->fieldName = $fieldName;

        return $this;
    }
}
