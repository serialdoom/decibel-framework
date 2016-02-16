<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * A stream to which data can be written.
 *
 * @author        Timothy de Paris
 */
interface DWritableStream
{
    /**
     * Erases any content existing within the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be erased.
     */
    public function erase();

    /**
     * Writes data to the stream.
     *
     * @param    string $data Data to write to the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function write($data);
}
