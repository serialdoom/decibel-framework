<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * A stream in which data can be read or written to a specific position.
 *
 * @author        Timothy de Paris
 */
interface DSeekableStream extends DReadableStream
{
    /**
     * Returns the number of bytes contained in the stream.
     *
     * @return    int        The length of the stream in bytes.
     */
    public function getLength();

    /**
     * Returns the current pointer position.
     *
     * @return    int
     * @throws    DStreamSeekException    If the pointer position could
     *                                    not be retrieved.
     */
    public function getPosition();

    /**
     * Moves the pointer to the start of the stream.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function rewind();

    /**
     * Moves the pointer within the stream to a specified position.
     *
     * @param    int $position New pointer position.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function seek($position);
}
