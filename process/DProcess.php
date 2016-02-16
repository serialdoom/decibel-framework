<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DInvalidProcessIdException;
use app\decibel\server\DServer;

/**
 * Abstracts a running process.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DProcess
{
    /**
     * The process ID.
     *
     * @var        int
     */
    protected $pid;
    /**
     * The executed command.
     *
     * @var        string
     */
    protected $command;
    /**
     * Reference to the server class.
     *
     * @var        DServer
     */
    protected $server;

    /**
     * Creates a DProcess object.
     *
     * @param    int $pid The process ID.
     *
     * @return    DProcess
     * @throws    DInvalidProcessIdException    If no process exists with
     *                                        the specified ID of the provided
     *                                        ID is not an integer.
     */
    protected function __construct($pid)
    {
        // Check if a process exists with the specified ID.
        $this->server = DServer::load();
        $this->command = $this->server->getCommandForPid((int)$pid);
        $this->pid = (int)$pid;
    }

    /**
     * Finds a running process based on process ID.
     *
     * @param    int $pid The process ID.
     *
     * @return    static
     * @throws    DInvalidProcessIdException    If no process exists with
     *                                        the specified ID.
     */
    public static function find($pid)
    {
        return new static($pid);
    }

    /**
     * Returns the executed command that created the process.
     *
     * @return    string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns the PID of the process.
     *
     * @return    int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Attempts to kill the process.
     *
     * @param    bool $force Whether to force terminiation of the process.
     *
     * @return    bool    <code>true</code> if the process was able
     *                    to be terminated, <code>false</code> otherwise.
     */
    public function kill($force = true)
    {
        if ($this->pid) {
            $success = $this->server->killProcess($this->pid, $force);
        } else {
            $success = false;
        }

        return $success;
    }
}
