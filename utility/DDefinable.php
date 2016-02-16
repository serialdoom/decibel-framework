<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DField;

/**
 * A class that can have paramters defined using
 * {@link app::decibel::model::field::DField DField} objects.
 *
 * @author        Timothy de Paris
 */
interface DDefinable
{
    /**
     * Returns the qualified name of the definition for this object.
     *
     * @return    string
     */
    public static function getDefinitionName();

    /**
     * Returns the field with the specified name.
     *
     * @param    string $name Name of the field.
     *
     * @return    DField
     * @throws    DInvalidPropertyException    If no field exists with the specified name.
     */
    public function getField($name);

    /**
     * Returns a list of fields defined by this object.
     *
     * @return    array    Array containing field names as keys
     *                    and {@link app::decibel::model::field::DField DField}
     *                    objects as values.
     */
    public function getFields();

    /**
     * Retrieves the value for a specified field.
     *
     * @param    string $fieldName Name of the field to set the value for.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     */
    public function getFieldValue($fieldName);

    /**
     * Returns an array containing the value of each defined field.
     *
     * @return    array    Array of field values with field names as keys.
     */
    public function getFieldValues();

    /**
     * Determines if this object has a field of the specified name.
     *
     * @param    string $name Name of the field.
     *
     * @return    bool
     */
    public function hasField($name);

    /**
     * Sets the value for a specified field.
     *
     * @param    string $fieldName Name of the field to set the value for.
     * @param    mixed  $value     The value to set.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for this cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setFieldValue($fieldName, $value);
}
