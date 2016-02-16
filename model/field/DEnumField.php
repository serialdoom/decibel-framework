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
class DEnumField extends DNumericField implements DEnumerated
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
            return null;
        }
        if (is_numeric($value)) {
            // Cast the value.
            settype($value, $this->getInternalDataType());
            if ($this->isValidValue($value)) {
                return $value;
            }
        }
        throw new DInvalidFieldValueException($this, $value);
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
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'integer';
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
     * @return    int
     */
    public function getStandardDefaultValue()
    {
        return null;
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
        return (!$this->values
            || array_key_exists($value, $this->values));
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->size = 1;
        $this->unsigned = true;
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
        $minimum = null;
        $maximum = null;
        if ($this->getValueBoundary($minimum, $maximum)
            && (min($keys) < $minimum
                || max($keys) > $maximum)
        ) {
            throw new DInvalidParameterValueException(
                self::FIELD_VALUES,
                array(__CLASS__, __FUNCTION__),
                "Array containing numeric keys within the range {$minimum} to {$maximum}. Use <code>app\\decibel\\model\\field\\DEnumField::setSize()</code> to increase the size of this field."
            );
        }
        // Check that all array keys are integers.
        if (count(array_filter($keys, 'is_string')) !== 0) {
            throw new DInvalidParameterValueException(
                self::FIELD_VALUES,
                array(__CLASS__, __FUNCTION__),
                "Array containing integer keys."
            );
        }
        $this->setArray(self::FIELD_VALUES, $values);

        return $this;
    }
}
