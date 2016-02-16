<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DNumericField;
use app\decibel\regional\DLabel;
use app\DecibelCMS\Widget\BooleanWidget;

/**
 * Represents a field that can contain a boolean value.
 *
 * @author        Timothy de Paris
 */
class DBooleanField extends DNumericField
{
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
        if ($this->nullOption !== null
            && $this->isNull($value)
        ) {
            $value = null;
        } else {
            if (is_string($value)
                && strtolower($value) === 'no'
            ) {
                $value = false;
            } else {
                $value = (bool)$value;
            }
        }

        return $value;
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
        if (is_bool($data)) {
            $showType = true;
            $debug = $data;
        } else {
            $debug = parent::debugValue($data, $showType);
        }

        return $debug;
    }
    ///@endcond
    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    bool
     */
    public function getStandardDefaultValue()
    {
        if ($this->nullOption !== null) {
            $defaultValue = null;
        } else {
            $defaultValue = false;
        }

        return $defaultValue;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'boolean';
    }

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        return (bool)rand(0, 1);
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
        // Create field search object.
        $options = new DFieldSearch($this);
        $widget = $options->getWidget();
        $widget->mode = BooleanWidget::MODE_DROPDOWN;
        $widget->setNullOption('&nbsp;');
        $widget->setValue(null);

        return $options;
    }

    /**
     * Determines if the provided value is considered empty for this field.
     *
     * @param    mixed $value The value to test.
     *
     * @return    bool
     */
    public function isEmpty($value)
    {
        return $this->isNull($value);
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
     * Sets the maximum size (in bytes) of data that can be stored
     * against this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
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
            $stringValue = $this->nullOption;
        } else {
            if ($data) {
                $stringValue = new DLabel('app\decibel', 'yes');
            } else {
                $stringValue = new DLabel('app\decibel', 'no');
            }
        }

        return $stringValue;
    }
}
