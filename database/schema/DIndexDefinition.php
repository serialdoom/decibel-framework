<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DIndex;

/**
 * Provides information about an index.
 *
 * @author    Nikolay Dimitrov
 */
class DIndexDefinition extends DTableElementDefinition
{
    /**
     * 'Name' field name.
     *
     * @var        string
     */
    const FIELD_NAME = 'name';

    /**
     * 'Type' field name.
     *
     * @var        string
     */
    const FIELD_TYPE = 'type';

    /**
     * List of columns included in the index.
     *
     * @var        array
     */
    protected $columns;

    /**
     * Create a new {@link DIndexDefinition} object.
     *
     * @param    string $name    Index name
     * @param    string $type    Index type
     * @param    array  $columns Names of fields included in the index.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If provided paramters are not valid.
     */
    public function __construct($name = null, $type = null, array $columns = array())
    {
        parent::__construct(null);
        $this->setName($name);
        $this->setType($type);
        $this->setColumns($columns);
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
        $name = new DTextField(self::FIELD_NAME, 'Name');
        $name->setMaxLength(50);
        $this->addField($name);
        $type = new DTextField(self::FIELD_TYPE, 'Type');
        $type->setMaxLength(50);
        $this->addField($type);
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['columns'] = $this->columns;

        return $debug;
    }

    /**
     *
     * @param    DIndex $index
     *
     * @return    DIndexDefinition or null if fields equal zero.
     */
    public static function createFromDIndex(DIndex $index)
    {
        $name = $index->getName();
        $type = $index->getDatabaseIdentifier();
        $fields = $index->getNativeFieldNames();
        // Can't have an index without any fields.
        if (count($fields) === 0) {
            $definition = null;
        } else {
            $definition = new DIndexDefinition($name, $type, $fields);
        }

        return $definition;
    }

    /**
     * Adds a column to the index definition.
     *
     * @param    string $name Name of the column.
     *
     * @return    static
     */
    public function addColumn($name)
    {
        $this->columns[] = $name;

        return $this;
    }

    /**
     * Returns name of the index.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->getFieldValue(self::FIELD_NAME);
    }

    /**
     * Returns type of the index.
     *
     * @return    string
     */
    public function getType()
    {
        return $this->getFieldValue(self::FIELD_TYPE);
    }

    /**
     * Returns the columns included in this index.
     *
     * @return    array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Resets the values of all fields to their default value.
     *
     * @return    void
     */
    public function resetFieldValues()
    {
        parent::resetFieldValues();
        $this->columns = array();
    }

    /**
     * Sets the columns included in this index.
     *
     * @param    array $columns List of column names.
     *
     * @return    static
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets the index name.
     *
     * @param    string $name The index name.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setName($name)
    {
        $this->setFieldValue(self::FIELD_NAME, $name);

        return $this;
    }

    /**
     * Sets the index type.
     *
     * @param    string $type The index type.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setType($type)
    {
        $this->setFieldValue(self::FIELD_TYPE, $type);

        return $this;
    }
}
