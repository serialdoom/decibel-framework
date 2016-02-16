<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\server;

use app\decibel\process\DInvalidProcessIdException;

/**
 * Wrapper class for server information and access on a Windows server.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DWindowsServer extends DServer
{
    /**
     * The maximum number of files that may be opened on a Windows server.
     *
     * This is a hard-coded limit on the Windows operating system when using
     * the C runtime library. See http://msdn.microsoft.com/en-us/library/6e3b887c%28VS.80%29.aspx
     * for further information.
     *
     * @var        int
     */
    const OPEN_FILE_LIMIT = 2048;

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
        // Need to run tasklist on Windows.
        $matches = array();
        $processes = explode("\n", shell_exec("tasklist.exe /FO CSV /NH /FI \"PID eq {$pid}\" 2>&1"));
        foreach ($processes as $process) {
            if (preg_match("/^\"(.*)\",\"({$pid})\",\"(.*)\",\"(.*)\",\"(.*)\"$/", $process, $matches) === 1) {
                return $matches[1];
            }
        }
        // If nothing found, throw an exception.
        throw new DInvalidProcessIdException($pid);
    }

    /**
     * Returns the maximum number of files that may be opened
     * by a process on this server.
     *
     * @note
     * This limit is hard-coded on a windows server and therefore the value
     * of the {@link DWindowsServer::OPEN_FILE_LIMIT} constant will be returned.
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
        return static::OPEN_FILE_LIMIT;
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
        return getenv('USERNAME');
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
            $command = "taskkill.exe /F /PID {$pid}";
        } else {
            $command = "taskkill.exe /PID {$pid}";
        }
        $output = shell_exec($command);

        return (bool)preg_match('/^SUCCESS:/', $output);
    }
}
