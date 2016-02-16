<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Stores debugging information.
 *
 * @author        Timothy de Paris
 */
abstract class DDebuggingInformation
{
    /**
     * Name of the file in which the debug occurred.
     *
     * @var        string
     */
    private $file;

    /**
     * Line on which the debug occurred.
     *
     * @var        string
     */
    private $line;

    /**
     * Debugging message.
     *
     * @var        string
     */
    private $message;

    /**
     * Backtrace to where the debug occurred.
     *
     * @var        mixed
     */
    private $backtrace;

    /**
     * Returns the name and location of the file in which the event occurred.
     *
     * @return    string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the line of the file on which the event occurred.
     *
     * @return    int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Returns the event message.
     *
     * @return    string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the event backtrace.
     *
     * @return    mixed
     */
    public function getBacktrace()
    {
        return $this->backtrace;
    }

    /**
     * Sets the name and location of the file in which the event occurred.
     *
     * @param    string $file The filename.
     *
     * @return    static
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Sets the line of the file on which the event occurred.
     *
     * @param    int $line The line number.
     *
     * @return    static
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * Sets the event message.
     *
     * @param    string $message The event message.
     *
     * @return    static
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Sets the event backtrace.
     *
     * @param    mixed $backtrace The event backtrace.
     *
     * @return    static
     */
    public function setBacktrace($backtrace)
    {
        $this->backtrace = $backtrace;

        return $this;
    }
}
