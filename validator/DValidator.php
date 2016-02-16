<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\validator;

use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;

/**
 * Base class for validators.
 *
 * A validator is used to confirm that a supplied piece of data conforms
 * to particular rules specified within that validator.
 *
 * @author        Timothy de Paris
 */
abstract class DValidator
{
    /**
     * Validates data according to the rules of this validation type.
     *
     * @param    mixed      $data  The data to validate.
     * @param    DBaseModel $model The modelthis data is from, if available.
     * @param    DField     $field The field this data is from, if available.
     *
     * @return    array    An array of error messages, or null if validation was successful.
     */
    abstract public function validate($data, DBaseModel $model = null, DField $field = null);
}
