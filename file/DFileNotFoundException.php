<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

/**
 * Handles an exception occurring when attempting to access a non-existant file.
 *
 * @author        Timothy de Paris
 */
class DFileNotFoundException extends DFileSystemException
{
    /**
     * Creates a new {@link DFileNotFoundException}.
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
