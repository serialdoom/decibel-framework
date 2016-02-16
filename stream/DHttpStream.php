<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * A stream to which data can be written over HTTP.
 *
 * @author        Timothy de Paris
 */
interface DHttpStream extends DWritableStream
{
    /**
     * Flushes the content of the stream to the client.
     *
     * @return    void
     */
    public function flush();

    /**
     * Sets the value of a header to be sent to the client.
     *
     * @param    string $header Name of the header.
     * @param    string $value  Header value.
     *
     * @return    void
     */
    public function setHeader($header, $value = null);

    /**
     * Sets the value of a header to be sent to the client.
     *
     * @param array $headers array of $header => $value tuples
     *
     */
    public function setHeaders(array $headers);
}
