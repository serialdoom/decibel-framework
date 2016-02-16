<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * Handles an exception occurring when seeking within a stream.
 *
 * @author        Timothy de Paris
 */
class DStreamSeekException extends DStreamException
{
    /**
     * Creates a new {@link DStreamSeekException}.
     *
     * @param    DSeekableStream $stream  The stream the error occurred in.
     * @param    string          $message Description of the error.
     *
     * @return    static
     */
    public function __construct(DSeekableStream $stream, $message = '')
    {
        parent::__construct(array(
                                'stream'  => (string)$stream,
                                'message' => $message,
                            ));
    }
}
