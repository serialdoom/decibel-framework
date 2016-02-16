<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\debug\DDebug;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DDate;
use app\DecibelCMS\Widget\DateWidget;
use DateTime;
use Exception;

/**
 * Represents a field that can contain a date and time value.
 *
 * @author        Timothy de Paris
 */
class DDateTimeField extends DNumericField
{
    /**
     * Attempts to convert the provided data into a value that
     * can be assigned to a field of this type.
     *
     * @warning
     * This method will throw a {@link DInvalidFieldValueException}
     * if the provided value cannot be cast. This may be due to the data type
     * being incompatible, or the provided value not meeting specific criteria
     * of this field (for example, character length of a string).
     * In Production Mode, the exception will be automatically handled
     * and execution will continue.
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
            // For numeric values, just ensure it is an integer.
        } else {
            if (is_numeric($value)) {
                $castValue = (int)$value;
                // Otherwise, try to convert it to a timestamp.
            } else {
                try {
                    $date = new DateTime($value);
                    $castValue = $date->getTimestamp();
                } catch (Exception $exception) {
                    throw new DInvalidFieldValueException($this, $value);
                }
            }
        }

        return $castValue;
    }

    ///@cond INTERNAL
    /**
     * Provides debugging information about a value for this field.
     *
     * @param    mixed $data          Data to convert.
     * @param    bool  $showType      Pointer in which a decision about whether
     *                                the datatype of the debug message should
     *                                be shown will be returned.
     *
     * @return    string    The debugged data as a string.
     */
    public function debugValue($data, &$showType)
    {
        if ($this->nullOption !== null
            && $this->isNull($data)
        ) {
            $message = "NULL [{$this->nullOption}]";
        } else {
            $dataDebug = new DDebug($data);
            $dataMessage = $dataDebug->getMessage();
            $message = "{$dataMessage} [" . date(DDate::getDateTimeFormat(), $data) . ']';
        }
        $showType = false;

        return $message;
    }
    ///@endcond
    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        // Determine the probability of past or future date.
        $probability = rand(0, 9);
        // Return a past date.
        if ($probability < 7) {
            return rand(time() - 2592000, time());
        }

        // Return a future date.
        return rand(time(), time() + 86400);
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    int        The current UNIX timestamp.
     */
    public function getStandardDefaultValue()
    {
        if ($this->nullOption !== null) {
            $default = null;
        } else {
            $default = time();
        }

        return $default;
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
        // Use the date widget instead of the date time widget
        // when searching, as searching with a time can be too specific.
        $options = new DFieldSearch($this);
        $widget = new DateWidget($this->name);
        $widget->setDisplayName($this->displayName);
        $widget->setDescription($this->description);
        $options->setFieldValue('widget', $widget);
        $options->setFieldValue('displayName', $this->displayName);
        $options->setFieldValue('name', $this->name);
        // Add operators.
        $operators = array(
            DFieldSearch::OPERATOR_EQUAL        => 'On',
            DFieldSearch::OPERATOR_LESS_THAN    => 'Before',
            DFieldSearch::OPERATOR_GREATER_THAN => 'After',
        );
        // Add custom null option
        if ($this->nullOption !== null) {
            $operators[ DFieldSearch::OPERATOR_IS_NULL ] = $this->nullOption;
        }
        $options->setFieldValue('operators', $operators);

        return $options;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->size = 4;
        $this->unsigned = false;
    }

    /**
     * Sets the maximum size (in bytes) of data that can be stored
     * against this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown
     * by this method.
     *
     * @param    int $size Number of bytes (1, 2, 3, 4 or 8).
     *
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @return    void
     */
    public function setSize($size)
    {
        throw new DReadOnlyParameterException('size', $this->name);
    }

    /**
     * Sets whether data for this field can be signed.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $unsigned Whether data for this field can be signed.
     *
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @return    void
     */
    public function setUnsigned($unsigned)
    {
        throw new DReadOnlyParameterException('unsigned', $this->name);
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
            return $this->nullOption;
        }

        return date(DDate::getDateTimeFormat(), $data);
    }
}
