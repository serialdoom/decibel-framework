<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Handles an exception occurring when a specified database name for a field
 * is reserved.
 *
 * Reserved field names are stored in the variable {@link DField::$reservedFieldNames}.
 *
 * @author        Timothy de Paris
 */
class DReservedFieldNameException extends DFieldException
{
    /**
     * Creates a new {@link DReservedFieldNameException}.
     *
     * @param    string $name Name of the field.
     *
     * @return    static
     */
    public function __construct($name)
    {
        parent::__construct(array(
                                'name' => $name,
                            ));
    }
}
