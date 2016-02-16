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
 * Validates an IP address. Empty strings are also considered valid.
 *
 * @author    Nikolay Dimitrov
 */
class DIpValidator extends DValidator
{
    /**
     * Returns the minimum integer value for the specified IP address part.
     *
     * @param    int $position The position of the part in the IP address.
     *
     * @return    int
     */
    protected function getMinimumFor($position)
    {
        if ($position === 1
            || $position === 4
        ) {
            $minimum = 1;
        } else {
            $minimum = 0;
        }

        return $minimum;
    }

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
        // Empty strings are also valid.
        if (strlen($data) > 0) {
            $parts = explode('.', $data);
            if (!$this->validateParts($parts)) {
                $errors[] = '#fieldName# is not a valid IP address.';
            }
        }

        return $errors;
    }

    /**
     * Validates one part of an IP address.
     *
     * @param    int    $position The position of the part in the IP address.
     * @param    string $value    The part value.
     *
     * @return    boolean    <code>true</code> if the part is valid,
     *                    <code>false</code> if not.
     */
    protected function validatePart($position, $value)
    {
        // Everything else must be between 0..1 and 255.
        $minimum = $this->getMinimumFor($position);

        return is_numeric($value)
        && $value <= 255
        && $value >= $minimum;
    }

    /**
     * Validates an exploded IP address.
     *
     * @param    array $parts The IP address parts.
     *
     * @return    boolean    <code>true</code> if the parts are valid,
     *                    <code>false</code> if not.
     */
    protected function validateParts(array $parts)
    {
        if (count($parts) !== 4) {
            $valid = false;
        } else {
            $valid = true;
            foreach ($parts as $position => $value) {
                if (!$this->validatePart($position + 1, $value)) {
                    $valid = false;
                    break;
                }
            }
        }

        return $valid;
    }
}
