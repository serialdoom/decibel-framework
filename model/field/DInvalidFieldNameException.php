<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Handles an exception occurring when a specified database name for a field
 * is invalid.
 *
 * @author        Timothy de Paris
 */
class DInvalidFieldNameException extends DFieldException
{
    /**
     * Creates a new {@link DInvalidFieldNameException}.
     *
     * @param    string $name The field name.
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
