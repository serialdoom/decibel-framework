<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\archive;

/**
 * Handles an exception occurring when an attempt is made to overwrite
 * an existing archive file.
 *
 * @author        Timothy de Paris
 */
class DArchiveExistsException extends DArchiveException
{
    /**
     * Creates a new {@link DArchiveExistsException}.
     *
     * @param    string $filename The archive file name.
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
