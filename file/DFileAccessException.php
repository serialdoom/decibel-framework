<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

/**
 * Handles an exception occurring when access to a file is not available.
 *
 * @author        Timothy de Paris
 */
class DFileAccessException extends DFileSystemException
{
    /**
     * Creates a new {@link DFileAccessException}.
     *
     * @param    string $filename The file that cannot be accessed.
     * @param    string $reason   Optional reason for the access issue.
     *
     * @return    static
     */
    public function __construct($filename, $reason = '')
    {
        parent::__construct(array(
                                'filename' => $filename,
                                'reason'   => $reason,
                            ));
    }
}
