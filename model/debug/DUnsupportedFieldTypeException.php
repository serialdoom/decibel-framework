<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

use app\decibel\model\DDefinition;
use app\decibel\model\field\DField;

/**
 * Handles an exception occurring when an unsupported field type is added
 * to a definition.
 *
 * @author        Timothy de Paris
 */
class DUnsupportedFieldTypeException extends DModelException
{
    /**
     * Creates a new {@link DUnsupportedFieldTypeException}.
     *
     * @param    DField      $field
     * @param    DDefinition $definition
     *
     * @return  static
     */
    public function __construct(DField $field, DDefinition $definition)
    {
        parent::__construct(array(
                                'field'      => get_class($field),
                                'definition' => get_class($definition),
                            ));
    }
}
