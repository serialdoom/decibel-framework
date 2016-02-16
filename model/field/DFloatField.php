<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DReadOnlyParameterException;

/**
 * Represents a field that can contain an floating point value.
 *
 * @author        Timothy de Paris
 */
class DFloatField extends DNumericField
{
    /**
     * If provided, values will be rounded to the specified number
     * of decimal places.
     *
     * @var        int
     */
    protected $precision = null;

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DField::DATA_TYPE_FLOAT;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @note
     * For historical reasons "double" is returned, and not simply "float".
     * See http://php.net/gettype for further details.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'double';
    }

    /**
     * Returns the number of decimal places to which values of this field will be rounded.
     *
     * @return    int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->unsigned = false;
    }

    /**
     * Sets the number of decimal places to which values of this field will be rounded.
     *
     * @param    int $precision Number of decimal places.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setPrecision($precision)
    {
        $this->setInteger('precision', $precision);

        return $this;
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
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
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
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    DNumericField
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setUnsigned($unsigned)
    {
        throw new DReadOnlyParameterException('unsigned', $this->name);
    }
}
