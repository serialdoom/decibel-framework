<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\model\debug\DInvalidFieldValueException;

/**
 * Represents a field that can contain a numeric value.
 *
 * @author        Timothy de Paris
 */
abstract class DNumericField extends DField
{
    /**
     * Option specifying the maximum size of field data in bytes.
     *
     * An integer in the range specified in {@link DNumericField::$sizes}
     * must be provided.
     *
     * @var        int
     */
    protected $size;

    /**
     * Option denoting whether data for can be signed.
     *
     * @var        boolean
     */
    protected $unsigned = true;

    /**
     * Option denoting whether value will be auto incremented.
     *
     * @var        boolean
     */
    protected $autoincrement = false;

    /**
     * Available values for the {@link DNumericField::$size} option.
     *
     * @var        array
     */
    protected static $sizes = array(1, 2, 3, 4, 8);

    /**
     * Mapping of available sizes to the corresponding database field type.
     *
     * @var        array
     */
    protected static $sizeDataTypes = array(
        1 => DField::DATA_TYPE_TINYINT,
        2 => DField::DATA_TYPE_SMALLINT,
        3 => DField::DATA_TYPE_MEDIUMINT,
        4 => DField::DATA_TYPE_INT,
        8 => DField::DATA_TYPE_BIGINT,
    );

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
            if ($this->nullOption !== null) {
                $value = null;
            } else {
                $value = 0;
            }

            return $value;
        }
        // Values must be numeric.
        if (is_numeric($value)) {
            // Cast the value.
            settype($value, $this->getInternalDataType());
            // Check that it falls within the boundary for this field.
            $minimum = null;
            $maximum = null;
            if (!$this->getValueBoundary($minimum, $maximum)
                || ($value <= $maximum && $value >= $minimum)
            ) {
                return $value;
            }
        }
        throw new DInvalidFieldValueException($this, $value);
    }

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DNumericField::$sizeDataTypes[ $this->size ];
    }

    /**
     * Returns a representation of the database column required
     * to store data for this field.
     *
     * @return    DColumnDefinition
     */
    public function getDefinition()
    {
        return parent::getDefinition()
                     ->setUnsigned($this->unsigned)
                     ->setType($this->getDataType())
                     ->setAutoIncrement($this->autoincrement);
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
     * Returns a human-readable description of the internal data type
     * requirements of this field.
     *
     * This description is used by the {@link DInvalidFieldValueException}
     * class when thrown by the {@link DField::castValue()} method.
     *
     * @return    string
     */
    public function getInternalDataTypeDescription()
    {
        $description = $this->getInternalDataType();
        $minimum = null;
        $maximum = null;
        if ($this->getValueBoundary($minimum, $maximum)) {
            $description .= " between {$minimum} and {$maximum}";
        }

        return $description;
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
        return '[0-9]+';
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
        // Create search options descriptor.
        $options = new DFieldSearch($this);
        // Add operators.
        $options->setFieldValue('operators', array(
            DFieldSearch::OPERATOR_EQUAL        => 'Equal To',
            DFieldSearch::OPERATOR_LESS_THAN    => 'Less Than',
            DFieldSearch::OPERATOR_GREATER_THAN => 'Greater Than',
        ));

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
        if ($this->nullOption !== null) {
            return null;
        }

        return 0;
    }

    /**
     * Calculates the minimum and maximum values that may be assigned
     * to this field.
     *
     * @param    int $minimum         The minimum value that may be assigned
     *                                as a value of this field.
     * @param    int $maximum         The maximum value that may be assigned
     *                                as a value of this field.
     *
     * @return    bool    <code>true</code> if there is a bounday,
     *                    <code>false</code> if not.
     */
    public function getValueBoundary(&$minimum, &$maximum)
    {
        if ($this->getInternalDataType() !== 'integer') {
            return false;
        }
        $maximum = pow(2, $this->size * 8) - 1;
        $minimum = 0;
        if (!$this->unsigned) {
            $maximum = floor($maximum / 2);
            $minimum = ($maximum * -1) - 1;
        }

        return true;
    }

    /**
     * Sets whether this field increments automatically.
     *
     * @param    bool $autoincrement Whether this field increments automatically.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setAutoincrement($autoincrement)
    {
        $this->setBoolean('autoincrement', $autoincrement);

        return $this;
    }

    /**
     * Sets the maximum size (in bytes) of data that can be stored
     * against this field.
     *
     * @param    int $size Number of bytes (1, 2, 3, 4 or 8).
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setSize($size)
    {
        $this->setEnum(
            'size',
            $size,
            array_combine(
                DNumericField::$sizes,
                DNumericField::$sizes
            )
        );

        return $this;
    }

    /**
     * Sets whether data for this field can be signed.
     *
     * @param    bool $unsigned Whether data for this field can be signed.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setUnsigned($unsigned)
    {
        $this->setBoolean('unsigned', $unsigned);

        return $this;
    }

    /**
     * Converts a data value for this field to its string equivalent.
     *
     * @param    mixed $data The data to convert.
     *
     * @return    string    The string value of the data.
     */
    public function toString($data)
    {
        if ($this->nullOption !== null
            && $data === null
        ) {
            $stringValue = $this->nullOption;
        } else {
            $stringValue = (string)$data;
        }

        return $stringValue;
    }
}
