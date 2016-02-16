<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\database\schema\DColumnDefinition;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\debug\DInvalidFieldValueException;

/**
 * Represents a field that can contain a choice from pre-defined values.
 *
 * @author        Timothy de Paris
 */
class DEnumStringField extends DStringField implements DEnumerated
{
    use DEnumeratedField;

    /**
     * 'Values' field name.
     *
     * @var        string
     */
    const FIELD_VALUES = 'values';

    /**
     * Attempts to convert the provided data into a value that
     * can be assigned to a field of this type.
     *
     * @param    mixed $value The value to cast.
     *
     * @return    mixed    The cast value
     * @throws    DInvalidFieldValueException    If the provided value cannot
     *                                        be cast for this field.
     */
    public function castValue($value)
    {
        if ($this->isNull($value)) {
            $castValue = null;
        } else {
            if ($this->isValidValue((string)$value)) {
                $castValue = (string)$value;
            } else {
                throw new DInvalidFieldValueException($this, $value);
            }
        }

        return $castValue;
    }

    /**
     * Returns a DColumnDefinition object representing the database column
     * needed for storing this field.
     *
     * This overrides the parent method to ensure that enumerated fields
     * can always support a null value.
     *
     * @return    DColumnDefinition
     */
    public function getDefinition()
    {
        return parent::getDefinition()
                     ->setNull(true);
    }

    /**
     * Returns a regular expression that can be used to match the string
     * version of data for this field.
     *
     * @return    string    A regular expression, or <code>null</code> if it is
     *                    not possible to match via a regular expression.
     */
    public function getRegex()
    {
        return '(' . implode('|', array_keys($this->values)) . ')';
    }

    /**
     * Returns information about how the fields used by this index can be searched.
     *
     * @return    DFieldSearch    The object describing how search can be
     *                                performed, or null if search is not allowed
     *                                or possible.
     */
    public function getSearchOptions()
    {
        $options = new DFieldSearch($this);
        $widget = $options->getWidget();
        $widget->multiple = true;
        $widget->setNullOption('');

        return $options;
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    string
     */
    public function getStandardDefaultValue()
    {
        return null;
    }

    /**
     * Returns the values available for this field.
     *
     * @param    bool $flatten        If <code>true</code>, multi-dimensional
     *                                values will be flattened before being returned.
     *
     * @return    array
     */
    public function getValues($flatten = false)
    {
        if (!$flatten) {
            return $this->values;
        }
        $values = array();
        foreach ($this->values as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $values[ $subKey ] = $subValue;
                }
            } else {
                $values[ $key ] = $value;
            }
        }

        return $values;
    }

    /**
     * Determines if the provided value is a valid option
     * for this enumerated field.
     *
     * @param    mixed $value The value to test.
     *
     * @return    bool
     */
    public function isValidValue($value)
    {
        if (!$this->values) {
            $valid = true;
        } else {
            $values = $this->getValues(true);
            $valid = array_key_exists($value, $values);
        }

        return $valid;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->maxLength = 50;
    }

    /**
     * Sets the values available for this field.
     *
     * @param    array $values        An array containing database values as keys
     *                                and option descriptions as values.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setValues(array $values)
    {
        // Check that the values contain only numeric keys,
        // and that the keys are valid for the field size.
        $keys = array_keys((array)$values);
        $maximum = $this->maxLength;
        foreach ($keys as $key) {
            if (!is_string($key)
                || strlen($key) > $this->maxLength
            ) {
                throw new DInvalidParameterValueException(
                    self::FIELD_VALUES,
                    array(__CLASS__, __FUNCTION__),
                    "Array containing string keys up to {$maximum} characters in length. Use <code>app\\decibel\\model\\field\\DEnumStringField::setMaxLength()</code> to increase the size of this field."
                );
            }
        }
        $this->setArray(self::FIELD_VALUES, $values);

        return $this;
    }
}
