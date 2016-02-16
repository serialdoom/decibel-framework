<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DException;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DUtilityData;

/**
 * Represents a field that can contain
 * a {@link app::decibel::utility::DUtilityData DUtilityData} object.
 *
 * @author        Timothy de Paris
 */
class DUtilityDataField extends DField
{
    /**
     * Option specifying the qualified name of the type
     * of {@link app::decibel::utility::DUtilityData DUtilityData}
     * object that can be assigned to this field.
     *
     * @var        string
     */
    protected $linkTo;

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
        $linkTo = $this->linkTo;
        if ($this->isNull($value)) {
            $castValue = null;
        } else {
            if ($value instanceof $linkTo) {
                $castValue = $value;
                // Try to convert arrays to an object, as long as a specific
                // type of utility data is being linked to.
            } else {
                if (is_array($value)
                    && $linkTo !== DUtilityData::class
                ) {
                    try {
                        $castValue = $linkTo::fromArray($value);
                        // If anything goes wrong (invalid field, invalid field value
                        // or read-only field), the value is invalid.
                    } catch (DException $exception) {
                        throw new DInvalidFieldValueException($this, $value);
                    }
                    // Anything else is invalid.
                } else {
                    throw new DInvalidFieldValueException($this, $value);
                }
            }
        }

        return $castValue;
    }

    /**
     * Compares to values for this field to determine if they are equal.
     *
     * @param    mixed $value1 The first value.
     * @param    mixed $value2 The second value.
     *
     * @return    bool    true if the values are equal, false otherwise.
     */
    public function compareValues($value1, $value2)
    {
        $value1 = $this->serialize($value1);
        $value2 = $this->serialize($value2);

        return (serialize($value1) === serialize($value2));
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
        if ($data instanceof DUtilityData) {
            $showType = false;
            $debug = $data->generateDebug();
        } else {
            $debug = parent::debugValue($data, $showType);
        }

        return $debug;
    }
    ///@endcond
    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DField::DATA_TYPE_MEDIUMTEXT;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return $this->linkTo;
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    array    The default values.
     */
    public function getStandardDefaultValue()
    {
        if ($this->nullOption === null
            && $this->linkTo !== DUtilityData::class
        ) {
            $defaultValue = new $this->linkTo();
        } else {
            $defaultValue = null;
        }

        return $defaultValue;
    }

    /**
     * Determines if this field can be used for ordering.
     *
     * @return    bool
     */
    public function isOrderable()
    {
        return false;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->linkTo = DUtilityData::class;
        $this->exportable = false;
        $this->randomisable = false;
    }

    /**
     * Sets the type of utility data this field will contain.
     *
     * @param    string $linkTo   Qualified name of the utility data class
     *                            this field will contain.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setLinkTo($linkTo)
    {
        $this->setQualifiedName('linkTo', $linkTo, DUtilityData::class);

        return $this;
    }

    /**
     * Sets whether data for this field can be randomised for testing purposes.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $randomisable       Whether data for this field
     *                                    can be randomised.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setRandomisable($randomisable)
    {
        throw new DReadOnlyParameterException('randomisable', $this->name);
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
        if (method_exists($this->linkTo, '__toString')) {
            $stringValue = (string)$data;
        } else {
            $stringValue = '';
        }

        return $stringValue;
    }
}
