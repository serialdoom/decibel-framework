<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * Handles an exception occurring when reading from a stream.
 *
 * @author        Timothy de Paris
 */
class DStreamReadException extends DStreamException
{
    /**
     * Creates a new {@link DStreamReadException}.
     *
     * @param    DReadableStream $stream  The stream the error occurred in.
     * @param    string          $message Description of the error.
     *
     * @return    static
     */
    public function __construct(DReadableStream $stream, $message = '')
    {
        parent::__construct(array(
                                'stream'  => (string)$stream,
                                'message' => $message,
                            ));
    }
}
