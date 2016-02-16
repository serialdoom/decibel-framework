<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DField;

/**
 * Implementation of the {@link DDefinable} instance that can be incorporated
 * into a standard class to provide definable field functionality.
 *
 * This method also fulfills the requirements of the {@link app::decibel::debug::DDebuggable DDebuggable} interface.
 *
 * @author        Timothy de Paris
 */
trait DDefinableObject
{
    /**
     * Parameter definitions for this remote procedure.
     *
     * @var        array
     */
    public $fields;
    /**
     * Cache of unserialized field values.
     *
     * @var        array
     */
    protected $fieldPointers = array();
    /**
     * Provided field values.
     *
     * @var        array
     */
    protected $fieldValues = array();
    /**
     * List containing the original value for any field that has had it's value changed
     * using {@link DDefinableObject::setFieldValue()}
     *
     * @var        array
     */
    protected $originalValues = array();
    ///@cond INTERNAL
    /**
     * Handles retrieval of defined field values.
     *
     * @param    string $name The name of the field to retrieve.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @deprecated    In favour of {@link DDefinableObject::getFieldValue()}
     */
    public function __get($name)
    {
        $class = get_class($this);
        DErrorHandler::notifyDeprecation(
            "{$class}::\${$name}",
            "{$class}::getFieldValue()"
        );

        return $this->getFieldValue($name);
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Handles setting of defined field values.
     *
     * @note
     * All field values are read-only for remote procedures. Attempting
     * to use this method will result in a {@link app::decibel::debug::DReadOnlyParameterException
     * DReadOnlyParameterException} being thrown.
     *
     * @param    string $name  Name of the field to set the value for.
     * @param    mixed  $value The value to set.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for this cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     * @deprecated    In favour of {@link DRemoteProcedure::setFieldValue()}
     */
    public function __set($name, $value)
    {
        $class = get_class($this);
        DErrorHandler::notifyDeprecation(
            "{$class}::\${$name}",
            "{$class}::setFieldValue()"
        );

        return $this->setFieldValue($name, $value);
    }
    ///@endcond
    /**
     * Adds a field to the definition.
     *
     * @param    DField $field Definition of the field to add.
     *
     * @return    void
     */
    protected function addField(DField $field)
    {
        $field->setModelInformation(get_class($this));
        $this->fields[ $field->getName() ] = $field;
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    string
     */
    public function generateDebug()
    {
        $values = array();
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            $showType = true;
            $value = $field->debugValue($this->getFieldValue($fieldName), $showType);
            if ($showType === true) {
                $values[ $fieldName ] = $value;
            } else {
                $values[ '*' . $fieldName ] = $value;
            }
        }

        return $values;
    }

    /**
     * Returns the qualified name of the definition for this object.
     *
     * @return    string
     */
    public static function getDefinitionName()
    {
        return null;
    }

    /**
     * Returns the field with the specified name.
     *
     * @param    string $name Name of the field.
     *
     * @return    DField
     * @throws    DInvalidPropertyException    If no field exists with the specified name.
     */
    public function getField($name)
    {
        if (!isset($this->fields[ $name ])) {
            throw new DInvalidPropertyException($name, get_class($this));
        }

        return $this->fields[ $name ];
    }

    /**
     * Returns a list of fields defined by this object.
     *
     * @return    array    Array containing field names as keys
     *                    and {@link app::decibel::model::field::DField DField}
     *                    objects as values.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the names of fields in the definition of the specified type.
     *
     * @param    string $type Qualfiied name of a class extending {@link DField}.
     *
     * @return    array    List of {@link DField} objects, with field names as keys.
     */
    public function getFieldsOfType($type)
    {
        $fields = array();
        foreach ($this->fields as $field) {
            /* @var $field DField */
            if ($field instanceof $type) {
                $fieldName = $field->getName();
                $fields[ $fieldName ] = $field;
            }
        }

        return $fields;
    }

    /**
     * Retrieves the value for a specified field.
     *
     * @param    string $fieldName Name of the field to set the value for.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     */
    public function getFieldValue($fieldName)
    {
        $field = $this->getField($fieldName);
        // Check if field needs to be unserialized.
        if (!array_key_exists($fieldName, $this->fieldPointers)) {
            $this->setFieldPointer($field);
        }

        // Return field value.
        return $this->fieldPointers[ $fieldName ];
    }

    /**
     * Returns an array containing the value of each defined field.
     *
     * @return    array    Array of field values with field names as keys.
     */
    public function getFieldValues()
    {
        $values = array();
        foreach (array_keys($this->fields) as $fieldName) {
            $values[ $fieldName ] = $this->getFieldValue($fieldName);
        }

        return $values;
    }

    /**
     * Determines if this object has a field of the specified name.
     *
     * @param    string $name Name of the field.
     *
     * @return    bool
     */
    public function hasField($name)
    {
        return isset($this->fields[ $name ]);
    }

    /**
     * Determines if this object has a field of the specified type.
     *
     * @param    string $type Qualfiied name of a class extending {@link DField}.
     *
     * @return    bool    <code>true</code> if this object has a field of the specified type,
     *                    <code>false</code> if not.
     */
    public function hasFieldOfType($type)
    {
        $hasField = false;
        foreach ($this->fields as $field) {
            /* @var $field DField */
            if ($field instanceof $type) {
                $hasField = true;
                break;
            }
        }

        return $hasField;
    }

    /**
     * Resets the values of all fields to their default value.
     *
     * @return    void
     */
    public function resetFieldValues()
    {
        foreach ($this->fields as $fieldName => $field) {
            /* @var $field DField */
            $this->setFieldValue($fieldName, $field->getDefaultValue());
        }
    }

    /**
     * Sets the value for a specified field.
     *
     * @param    string $fieldName Name of the field to set the value for.
     * @param    mixed  $value     The value to set.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for this cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setFieldValue($fieldName, $value)
    {
        // Load the field to validate that it exists.
        $field = $this->getField($fieldName);
        // Don't update read-only fields.
        // @todo this causes problems with stringValue, etc.
        //		if ($field->isReadOnly()) {
        //			throw new DReadOnlyParameterException($fieldName, get_class($this));
        //		}
        // Cast the value to ensure it is compatible,
        // this will also serialize it.
        $castValue = $field->castValue($value);
        // Store the original value of the field.
        $this->storeOriginalValue($field, $castValue);
        // Store the serialized field value.
        $this->fieldValues[ $fieldName ] = $castValue;
        unset($this->fieldPointers[ $fieldName ]);
    }

    /**
     * Stores the unserialized field value for the specified field within
     * the field pointers list.
     *
     * @param    DField $field
     *
     * @return    void
     */
    protected function setFieldPointer(DField $field)
    {
        $fieldName = $field->getName();
        if (array_key_exists($fieldName, $this->fieldValues)) {
            $value = $this->fieldValues[ $fieldName ];
            $mapper = $field->getDatabaseMapper();
            $this->fieldPointers[ $fieldName ] = $mapper->unserialize($value);
            // If there is no value set, set the default value
            // into the field pointers (it does not need to be unserialized).
        } else {
            $this->fieldPointers[ $fieldName ] = $field->getDefaultValue();
        }
    }

    /**
     * Stores the original value of a field when it is changed through
     * the {@link DDefinableObject::setFieldValue()} method.
     *
     * @param    DField $field    The field being updated.
     * @param    mixed  $newValue The new field value.
     *
     * @return    void
     */
    protected function storeOriginalValue(DField $field, $newValue)
    {
        $fieldName = $field->getName();
        if (!array_key_exists($fieldName, $this->originalValues)
            && (!array_key_exists($fieldName, $this->fieldValues)
                || !$field->compareValues($newValue, $this->fieldValues[ $fieldName ]))
        ) {
            if (array_key_exists($fieldName, $this->fieldValues)) {
                $this->originalValues[ $fieldName ] = $this->fieldValues[ $fieldName ];
            } else {
                $this->originalValues[ $fieldName ] = null;
            }
        }
    }
}
