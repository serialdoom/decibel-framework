<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DDebug;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\regional\DLabel;
use app\decibel\utility\DString;

/**
 * Implementation of the {@link DEnumerated} interface for {@link DField} objects.
 *
 * @author    Timothy de Paris
 */
trait DEnumeratedField
{
    /**
     * Specifies available values for enumerated fields.
     *
     * The value of this option must be an array of the form
     * <code>array(database value => display value, ...)</code>
     *
     * @var        array
     */
    protected $values = array();

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
        $nullOption = $this->nullOption;
        if ($nullOption !== null
            && $data === null
        ) {
            $message = "NULL [{$nullOption}]";
        } else {
            $values = $this->getValues();
            $dataDebug = new DDebug($data);
            $dataMessage = $dataDebug->getMessage();
            if (!is_array($data)
                && isset($values[ $data ])
            ) {
                $message = "{$dataMessage} [{$values[$data]}]";
            } else {
                $unknownValue = DLabel::translate(DField::class, 'unknownValue');
                $message = "{$dataMessage} [-- {$unknownValue} --]";
            }
        }
        $showType = false;

        return $message;
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
        $values = DString::implode(
            array_keys($this->getValues()),
            '</code>, <code>', '</code> or <code>'
        );

        return "One of <code>{$values}</code>";
    }

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        $values = $this->getValues();
        if (count($values) === 0) {
            $randomValue = null;
        } else {
            $keys = array_keys($values);
            $key = rand(0, count($keys) - 1);
            $randomValue = $keys[ $key ];
        }

        return $randomValue;
    }

    /**
     * Returns the values available for this field.
     *
     * @return    array
     */
    public function getValues()
    {
        return $this->values;
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
        $this->setArray('values', $values);

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
        if (isset($this->values[ $data ])) {
            $stringValue = $this->values[ $data ];
        } else {
            if ($this->nullOption !== null
                && $this->isNull($data)
            ) {
                $stringValue = $this->nullOption;
            } else {
                $stringValue = new DLabel(DField::class, 'unknownValue');
            }
        }

        return $stringValue;
    }
}
