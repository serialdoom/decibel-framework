<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DDate;
use DateTime;
use Exception;

/**
 * Represents a field that can contain a date value.
 *
 * @author        Timothy de Paris
 */
class DDateField extends DField
{
    /**
     * PHP date format for converting timestamps to {@link DDateField} values.
     *
     * @var        string
     */
    const FORMAT_DATE = 'Y-m-d';

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
        if ($this->isNull($value) || $value == '') {
            $castValue = null;
            // Timestamp.
        } else {
            if (is_numeric($value)) {
                $castValue = date(self::FORMAT_DATE, $value);
                // Correctly formatted date.
            } else {
                if (is_string($value)
                    && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)
                ) {
                    $castValue = $value;
                    // Try to convert any other value.
                } else {
                    try {
                        $date = new DateTime($value);
                        $castValue = date(
                            self::FORMAT_DATE,
                            $date->getTimestamp()
                        );
                    } catch (Exception $exception) {
                        throw new DInvalidFieldValueException($this, $value);
                    }
                }
            }
        }

        return $castValue;
    }

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DField::DATA_TYPE_DATE;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'string';
    }

    /**
     * Returns a random value suitable for assignment as the value
     * of this field.
     *
     * @note
     * There is a seventy-percent chance that this method will return
     * a date in the past.
     *
     * @return    string
     */
    public function getRandomValue()
    {
        // Determine the probability of past or future date.
        $probability = rand(0, 9);
        // Return a past date.
        if ($probability < 7) {
            $randomValue = date(self::FORMAT_DATE, rand(time() - 2592000, time()));
            // Return a future date.
        } else {
            $randomValue = date(self::FORMAT_DATE, rand(time(), time() + 86400));
        }

        return $randomValue;
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
        $widget = $options->getWidget();
        $widget->setNullOption(null);
        // Add operators.
        $operators = array(
            DFieldSearch::OPERATOR_EQUAL        => 'On',
            DFieldSearch::OPERATOR_LESS_THAN    => 'Before',
            DFieldSearch::OPERATOR_GREATER_THAN => 'After',
        );
        if ($this->nullOption !== null) {
            $operators[ DFieldSearch::OPERATOR_IS_NULL ] = $this->nullOption;
        }
        $options->setFieldValue('operators', $operators);

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
        if ($this->nullOption !== null) {
            $defaultValue = null;
        } else {
            $defaultValue = date(self::FORMAT_DATE);
        }

        return $defaultValue;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
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
            && $this->isNull($data)
        ) {
            $stringValue = $this->nullOption;
        } else {
            $stringValue = date(DDate::getDateFormat(), strtotime($data));
        }

        return $stringValue;
    }
}
