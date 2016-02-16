<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\validator;

use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;
use app\decibel\validator\DValidator;

/**
 * Validates a colour.
 *
 * Empty strings are also considered valid.
 *
 * @author        Mohammad Haque
 */
class DColourValidator extends DValidator
{
    /**
     * Regular expression used for validating a colour.
     *
     * @note
     * This regex allows empty strings.
     *
     * @var        string
     */
    const REGULAR_EXPRESSION = "/^(#[a-fA-F0-9]{6})?$/";

    /**
     * Validates data according to the rules of this validation type.
     *
     * @param    mixed      $data  The data to validate.
     * @param    DBaseModel $model The modelthis data is from, if available.
     * @param    DField     $field The field this data is from, if available.
     *
     * @return    array    An array of error messages, or null if validation was successful.
     */
    public function validate($data, DBaseModel $model = null, DField $field = null)
    {
        $errors = array();
        if (!preg_match(self::REGULAR_EXPRESSION, $data)) {
            $errors[] = '#fieldName# is not a valid html colour code.';
        }

        return $errors;
    }
}
