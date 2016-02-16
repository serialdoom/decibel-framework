<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStream;
use app\decibel\stream\DStreamReadException;
use app\decibel\stream\DStreamWriteException;
use app\decibel\stream\DWritableStream;

/**
 * Wraps a string to enable it to be used as a stream.
 *
 * @author        Timothy de Paris
 */
class DTextStream extends DStream
    implements DReadableStream, DWritableStream, DSeekableStream
{
    /**
     * The stream contents.
     *
     * @var        string
     */
    protected $content;

    /**
     * Current pointer position within the stream.
     *
     * @var        int
     */
    protected $pointer;

    /**
     * Creates a new {@link DTextStream}.
     *
     * @param    string $content Content with which to prime the stream.
     *
     * @return    static
     */
    public function __construct($content = '')
    {
        $this->content = $content;
        $this->pointer = 0;
    }

    /**
     * Closes the stream.
     *
     * @note
     * This method has no effect on this stream.
     *
     * @return    void
     */
    public function close()
    {
    }

    /**
     * Erases any content existing within the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be erased.
     */
    public function erase()
    {
        $this->content = '';
    }

    /**
     * Returns the number of bytes contained in the stream.
     *
     * @return    int        The length of the stream in bytes.
     */
    public function getLength()
    {
        return strlen($this->content);
    }

    /**
     * Returns the current pointer position.
     *
     * @return    int
     * @throws    DStreamSeekException    If the pointer position could
     *                                    not be retrieved.
     */
    public function getPosition()
    {
        return $this->pointer;
    }

    /**
     * Determines whether the stream is "open".
     *
     * @note
     * This method will always return <code>true</code>
     * for a {@link DTextStream}.
     *
     * @return    bool
     */
    public function isOpen()
    {
        return true;
    }

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
    public function read($length = null)
    {
        // Handle EOF.
        if ($this->pointer >= strlen($this->content)) {
            return null;
        }
        // Return the remaining stream data.
        if ($length === null) {
            $content = substr($this->content, $this->pointer);
            $length = strlen($content);
            // Return a specific amount of stream data.
        } else {
            $content = substr($this->content, $this->pointer, $length);
        }
        // Move the pointer forward.
        $this->pointer += $length;

        return $content;
    }

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
    public function readLine()
    {
        // Locate the first newline.
        $matches = null;
        $position = preg_match(
            '/[^\v]*\v+/',
            $this->content,
            $matches,
            0,
            $this->pointer
        );
        // If there is no newline, return everything.
        if (!$position) {
            return $this->read();
        }
        // Move the pointer forward.
        $this->pointer += strlen($matches[0]);

        return $matches[0];
    }

    /**
     * Moves the pointer to the start of the stream.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * Moves the pointer within the stream to a specified position.
     *
     * @param    int $position New pointer position.
     *
     * @return    void
     * @throws    DStreamSeekException    If the pointer could not be moved.
     */
    public function seek($position)
    {
        $this->pointer = $position;
    }

    /**
     * Writes data to the stream.
     *
     * @param    string $data Data to write to the stream.
     *
     * @return    void
     * @throws    DStreamWriteException    If the data could not be written.
     */
    public function write($data)
    {
        $this->content .= $data;
    }
}
