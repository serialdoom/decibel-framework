<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DStringField;
use app\decibel\utility\DString;

/**
 * Represents a field that can contain a text value.
 *
 * @author        Timothy de Paris
 */
class DTextField extends DStringField
{
    /**
     * Option specifying whether HTML is allowed for this field.
     *
     * @var        bool
     */
    protected $allowHtml = false;

    /**
     * Limits characters that can be entered.
     *
     * A string containing the allowed characters for this
     * field should be supplied.
     *
     * By default, no limit will be applied.
     *
     * @var        string
     */
    protected $charLimit;

    /**
     * Regular expression describing the strings that may be used as a value
     * for this field.
     *
     * @var        string
     */
    protected $validationRegex = '.*';

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
            $regex = "/^{$this->validationRegex}$/s";
            $castValue = (string)$value;
            if (!preg_match($regex, $castValue)) {
                throw new DInvalidFieldValueException($this, $value);
            }
        }

        return $castValue;
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
            $defaultValue = parent::getStandardDefaultValue();
        }

        return $defaultValue;
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
        if ($this->validationRegex) {
            $regex = $this->validationRegex;
        } else {
            $regex = '.';
            if ($this->maxLength) {
                $regex .= "{0,{$this->maxLength}}";
            } else {
                $regex .= '*';
            }
        }

        return $regex;
    }

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        $minLength = ($this->maxLength && $this->maxLength < 100)
            ? $this->maxLength - 10
            : 100;
        $maxLength = $this->maxLength
            ? $this->maxLength
            : 500;

        return DString::getRandomString(rand($minLength, $maxLength));
    }

    /**
     * Returns information about how the fields used by this index can be searched.
     *
     * @return    DFieldSearch    The object describing how search can be
     *                            performed, or null if search is not allowed
     *                            or possible.
     */
    public function getSearchOptions()
    {
        // Create search options descriptor.
        $options = new DFieldSearch($this);
        $options->setFieldValue('operator', DFieldSearch::OPERATOR_LIKE);
        $widget = $options->getWidget();
        $widget->setNullOption(null);

        return $options;
    }

    /**
     * Returns the regular expression describing the strings that may
     * be used as a value for this field.
     *
     * @return    string    Validation regular expression, or <code>null</code>
     *                    if any value is allowed.
     */
    public function getValidationRegex()
    {
        return $this->validationRegex;
    }

    /**
     * Specifies whether HTML content is allowed for this field.
     *
     * @param    bool $allowHtml Whether HTML content is allowed.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setAllowHtml($allowHtml)
    {
        $this->setBoolean('allowHtml', $allowHtml);

        return $this;
    }

    /**
     * Sets a list of characters that may be used within values for this field.
     *
     * @param    string $charLimit List of valid characters.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setCharLimit($charLimit)
    {
        $this->setString('charLimit', $charLimit, true);

        return $this;
    }

    /**
     * Sets the regular expression describing the strings that may
     * be used as a value for this field.
     *
     * @note
     * The provided regular expression should not contain enclosing forward
     * slashes (/) or start and end of string characters (^ and $ respectively).
     * These will automatically be added when validating field values.
     *
     * @param    string $validationRegex      Validation regular expression,
     *                                        or <code>null</code> to allow
     *                                        any value.
     *
     * @return    static
     */
    public function setValidationRegex($validationRegex = null)
    {
        if ($validationRegex === null) {
            $this->validationRegex = '.*';
        } else {
            $this->validationRegex = trim($validationRegex, '/');
        }

        return $this;
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
        $description = 'string';
        if ($this->maxLength !== null) {
            $description .= " with a maximum length of {$this->maxLength}";
        }
        if ($this->validationRegex !== '.*') {
            $description .= "  matching the regular expression <code>{$this->validationRegex}</code>";
        }

        return $description;
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
        if ($this->allowHtml) {
            $value = trim($value);
        } else {
            $value = trim($this->stripHtmlTags($value));
        }

        return empty($value);
    }

    /**
     * Prepares field data for saving to the database.
     *
     * @param    mixed $data The data to serialize.
     *
     * @return    mixed    The serialized data.
     */
    public function serialize($data)
    {
        if ($this->isNull($data)) {
            $serialized = null;
            // Strip HTML from the data as required.
        } else {
            if (!$this->allowHtml) {
                $serialized = $this->stripHtmlTags($data);
            } else {
                $serialized = (string)$data;
            }
        }

        return $serialized;
    }

    /**
     * Strips HTML from the provided value.
     *
     * @param    string $value The value to strip.
     *
     * @return    string    The stripped value.
     */
    protected function stripHtmlTags($value)
    {
        return trim(strip_tags($value));
    }
}
