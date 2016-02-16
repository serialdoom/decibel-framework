<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\database\schema\DColumnDefinition;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\debug\DInvalidFieldValueException;

/**
 * Represents a field that can contain a string value.
 *
 * @author        Timothy de Paris
 */
abstract class DStringField extends DField
{
    /**
     * Maximum length of up to 256 characters.
     *
     * @note
     * This will create a VARCHAR type field in the database if used
     * as the value of the maxLength option.
     *
     * @var        int
     */
    const LENGTH_256B = 255;

    /**
     * Maximum length of up to 2048 characters.
     *
     * @note
     * This will create a VARCHAR type field in the database if used
     * as the value of the maxLength option.
     *
     * @var        int
     */
    const LENGTH_2K = 2047;

    /**
     * Maximum length of up to 65,535 characters (64KB).
     *
     * @note
     * This will create a TEXT type field in the database if used
     * as the value of the maxLength option.
     *
     * @var        int
     */
    const LENGTH_64K = 65535;

    /**
     * Maximum length of up to 16,777,215 characters (16MB).
     *
     * @note
     * This will create a MEDIUMTEXT type field in the database if used
     * as the value of the maxLength option.
     *
     * @var        int
     */
    const LENGTH_16M = 16777215;

    /**
     * Option specifying the maximum number of character permissible in the data.
     *
     * If not specified, no maximum length will be applied.
     *
     * @var        int
     */
    protected $maxLength = null;

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
            if (is_string($value)) {
                $castValue = trim($value);
            } else {
                throw new DInvalidFieldValueException($this, $value);
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
        if ($this->maxLength === null
            || $this->maxLength > DStringField::LENGTH_64K
        ) {
            $dataType = DField::DATA_TYPE_MEDIUMTEXT;
        } else {
            if ($this->maxLength > DStringField::LENGTH_2K) {
                $dataType = DField::DATA_TYPE_TEXT;
            } else {
                $dataType = DField::DATA_TYPE_VARCHAR;
            }
        }

        return $dataType;
    }

    /**
     * Returns a DColumnDefinition object representing the database column
     * needed for storing this field or null.
     *
     * @return    DColumnDefinition
     */
    public function getDefinition()
    {
        $definition = parent::getDefinition();
        if ($this->maxLength <= DStringField::LENGTH_2K) {
            $definition->setSize($this->maxLength);
        }

        return $definition;
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
     * Returns the maximum number of characters allowed for strings assigned
     * as values of this field.
     *
     * @return    int        The maximum number of characters, or <code>null</code>
     *                    if no maximum length is set.
     */
    public function getMaxLength()
    {
        return $this->maxLength;
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
            $defaultValue = '';
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
        $this->maxLength = DStringField::LENGTH_64K;
    }

    /**
     * Sets the maximum number of characters allowed for strings assigned
     * as values of this field.
     *
     * @note
     * Text fields cannot have a maximum length greater than 16,777,215.
     * A {@link app::decibel::debug::DInvalidParameterValueException DInvalidParameterValueException}
     * will be thrown if a length greater than this is set.
     *
     * @param    int $maxLength       The maximum number of characters,
     *                                or <code>null</code> if no maximum length
     *                                applies.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setMaxLength($maxLength)
    {
        if ($maxLength < 1
            || $maxLength > DStringField::LENGTH_16M
        ) {
            throw new DInvalidParameterValueException(
                'maxLength',
                array(__CLASS__, __FUNCTION__),
                'A length between 1 and 16,777,215'
            );
        }
        $this->setInteger('maxLength', $maxLength);

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
        return (string)$data;
    }
}
