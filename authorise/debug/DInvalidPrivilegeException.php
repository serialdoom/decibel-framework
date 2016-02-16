<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\debug;

/**
 * Handles an exception occurring when an invalid privilege name is used.
 *
 * @author        Timothy de Paris
 */
class DInvalidPrivilegeException extends DAuthorisationException
{
    /**
     * Creates a new {@link DInvalidPrivilegeException}.
     *
     * @param    string $name Invalid privilege name.
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
