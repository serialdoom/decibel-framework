<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * Handles an exception occurring when writing to a stream.
 *
 * @author        Timothy de Paris
 */
class DStreamWriteException extends DStreamException
{
    /**
     * Creates a new {@link DStreamWriteException}.
     *
     * @param    DWritableStream $stream  The stream the error occurred in.
     * @param    string          $message Description of the error.
     *
     * @return    static
     */
    public function __construct(DWritableStream $stream, $message = '')
    {
        parent::__construct(array(
                                'stream'  => (string)$stream,
                                'message' => $message,
                            ));
    }
}
