<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 *
 *
 * @author        Timothy de Paris
 */
abstract class DStream
{
    /**
     * Ensures the stream is closed on destruction of the object.
     *
     * @return    void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Returns a string representation of the stream.
     *
     * @note
     * This does not return the content of the stream.
     *
     * @return    string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * Closes the stream.
     *
     * @return    void
     */
    abstract public function close();
}
