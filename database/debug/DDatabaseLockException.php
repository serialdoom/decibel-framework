<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when attempting to lock database tables.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DDatabaseLockException extends DDatabaseException
{
    /**
     * Creates a new {@link DDatabaseLockException}.
     *
     * @param    string $message Reason for the failure.
     *
     * @return    static
     */
    public function __construct($message)
    {
        parent::__construct(array(
                                'message' => $message,
                            ));
    }
}
