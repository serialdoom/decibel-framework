<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\server;

use app\decibel\process\DInvalidProcessIdException;

/**
 * Wrapper class for server information and access on a Linux server.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DLinuxServer extends DServer
{
    /**
     * Returns the maximum number of files that may be opened
     * by a process on this server.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $server = DServer::load();
     * debug($server->getOpenFileLimit());
     * @endcode
     *
     * Will output the maximum number of open files, such as <code>2048</code>
     *
     * @return    int        The maximium number of files that may be opened,
     *                    or <code>null</code> if it is not possible to determine.
     */
    public function getOpenFileLimit()
    {
        $limitOutput = trim(shell_exec('ulimit -n'));
        if (is_numeric($limitOutput)) {
            $limit = (int)$limitOutput;
        } else {
            $limit = null;
        }

        return $limit;
    }

    /**
     * Returns the command that was executed for the specified PID.
     *
     * @param    int $pid
     *
     * @return    string
     * @throws    DInvalidProcessIdException    If no process exists with the specified ID.
     */
    public function getCommandForPid($pid)
    {
        if (!file_exists("/proc/{$pid}")) {
            throw new DInvalidProcessIdException($pid);
        }

        return file_get_contents("/proc/{$pid}/cmdline");
    }

    /**
     * Returns the username of the user executing this script.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $server = DServer::load();
     * debug($server->getProcessUsername());
     * @endcode
     *
     * Will output a username such as <code>apache</code>
     *
     * @return    string
     */
    public function getProcessUsername()
    {
        $info = posix_getpwuid(posix_getuid());

        return $info['name'];
    }

    /**
     * Attempts to kill the process with the provided PID.
     *
     * @param    bool $force Whether to force terminiation of the process.
     *
     * @return    bool    <code>true</code> if the process was able
     *                    to be terminated, <code>false</code> otherwise.
     */
    public function killProcess($pid, $force = false)
    {
        if ($force) {
            $command = "kill -9 {$pid}";
        } else {
            $command = "kill {$pid}";
        }
        $output = shell_exec($command);

        return ($output === '');
    }
}
