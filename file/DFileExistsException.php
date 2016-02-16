<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

/**
 * Handles an exception occurring when a file being created already exists.
 *
 * @author        Timothy de Paris
 */
class DFileExistsException extends DFileSystemException
{
    /**
     * Creates a new {@link DFileExistsException}.
     *
     * @param    string $filename Name of the file.
     *
     * @return    static
     */
    public function __construct($filename)
    {
        parent::__construct(array(
                                'filename' => $filename,
                            ));
    }
}
