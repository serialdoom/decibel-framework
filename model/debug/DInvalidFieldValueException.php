<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

use app\decibel\debug\DDebug;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;

/**
 * Handles an exception where an invalid value is assigned to a model field.
 *
 * @author        Timothy de Paris
 */
class DInvalidFieldValueException extends DModelException
{
    /**
     * Creates a new {@link DInvalidFieldValueException}.
     *
     * @param    DField $field The field an invalid value was provided for.
     * @param    mixed  $value The provided value.
     *
     * @return    static
     */
    public function __construct(DField $field, $value)
    {
        $fieldName = $field->getName();
        $fieldOwner = $field->getOwner();
        if ($fieldOwner !== null) {
            $fieldName = "{$fieldOwner}::\${$fieldName}";
        }
        parent::__construct(array(
                                'value'     => $this->formatValue($value),
                                'fieldName' => $fieldName,
                                'expected'  => $field->getInternalDataTypeDescription(),
                            ));
    }

    /**
     * Formats a value for inclusion in the message for this exception.
     *
     * @param    mixed $value
     *
     * @return    string
     */
    protected function formatValue(&$value)
    {
        if ($value instanceof DBaseModel) {
            $formattedValue = get_class($value) . '(' . $value->getId() . ')';
        } else {
            if (is_object($value)) {
                $formattedValue = get_class($value);
            } else {
                $debug = new DDebug($value, false, false);
                $formattedValue = $debug->getMessage();
            }
        }

        return $formattedValue;
    }
}
