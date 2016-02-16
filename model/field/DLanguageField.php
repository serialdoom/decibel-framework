<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DTextField;
use app\decibel\regional\DLanguage;

/**
 * Represents a field that stores a language code.
 *
 * @author        Timothy de Paris
 */
class DLanguageField extends DTextField
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
        if ($this->isNull($value)) {
            return null;
        }
        // DLanguage instance.
        if ($value instanceof DLanguage) {
            return $value->getCode();
        }
        // Correctly formatted language code.
        if (is_string($value)
            && preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $value)
        ) {
            return $value;
        }
        throw new DInvalidFieldValueException($this, $value);
    }

    /**
     * Sets a list of characters that may be used within values for this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    string $charLimit List of valid characters.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setCharLimit($charLimit)
    {
        throw new DReadOnlyParameterException('charLimit', $this->name);
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->maxLength = 5;
        $this->charLimit = 'abcdefghijklmnopqrstuvwxyz-';
    }

    /**
     * Sets the maximum number of characters allowed for strings assigned
     * as values of this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    int $maxLength       The maximum number of characters,
     *                                or <code>null</code> if no maximum length
     *                                applies.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setMaxLength($maxLength)
    {
        throw new DReadOnlyParameterException('maxLength', $this->name);
    }
}
