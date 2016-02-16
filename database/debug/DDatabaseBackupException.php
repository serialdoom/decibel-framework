<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when performing a database backup.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DDatabaseBackupException extends DDatabaseException
{
    /**
     * Creates a new {@link DDatabaseBackupException}.
     *
     * @param    string $reason Reason for the failure.
     *
     * @return    static
     */
    public function __construct($reason)
    {
        parent::__construct(array(
                                'reason' => $reason,
                            ));
    }
}
