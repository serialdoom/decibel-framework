<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * A stream from which data can be read.
 *
 * @author        Timothy de Paris
 */
interface DReadableStream
{
    /**
     * Reads data from the stream.
     *
     * @param    int $length      The number of bytes of data to read,
     *                            or <code>null</code> to read all available data.
     *
     * @return    string    The data, or <code>null</code> if the end of
     *                    the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function read($length = null);

    /**
     * Reads a line of data from the stream.
     *
     * @note
     * The line ending will also be returned.
     *
     * @return    string    The data, or <code>null</code> if the end of
     *                    the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function readLine();
}
