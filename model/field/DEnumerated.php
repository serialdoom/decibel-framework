<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * A field that allows a selection of one or more values.
 *
 * @author    Timothy de Paris
 */
interface DEnumerated
{
    /**
     * Returns the values available for this field.
     *
     * @return    array
     */
    public function getValues();

    /**
     * Sets the values available for this field.
     *
     * @param    array $values        An array containing database values as keys
     *                                and option descriptions as values.
     *
     * @return    DEnumField
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setValues(array $values);
}

