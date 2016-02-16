<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\server;

use app\decibel\utility\DBaseClass;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Wrapper class for server information and access.
 *
 * @section       why Why Would I Use It?
 *
 * This class normalises access to information about various server level
 * features across different operating systems.
 *
 * @section       how How Do I Use It?
 *
 * This singleton class can be loaded as follows:
 *
 * @code
 * use app\decibel\server\DServer;
 *
 * $server = DServer::load();
 * @endcode
 *
 * Once loaded, methods can be called as documented below.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
abstract class DServer implements DSingleton
{
    use DBaseClass;
    use DSingletonClass;

    /**
     * Create the server object.
     *
     * @return    static
     */
    protected function __construct()
    { }

    /**
     * Returns the host name for the local machine.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $server = DServer::load();
     * debug($server->getHostName());
     * @endcode
     *
     * Will output a host name such as <code>myserver.decibelhosting.com</code>
     *
     * @return    string
     */
    public function getHostName()
    {
        return php_uname('n');
    }

    /**
     * Returns the command that was executed for the specified PID.
     *
     * @param    int $pid
     *
     * @return    string
     * @throws    DInvalidProcessIdException    If no process exists with the specified ID.
     */
    abstract public function getCommandForPid($pid);

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
    abstract public function getOpenFileLimit();

    /**
     * Returns the ID of the process executing this script.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $server = DServer::load();
     * debug($server->getProcessId());
     * @endcode
     *
     * Will output a process ID such as <code>1340</code>
     *
     * @return    int
     */
    public function getProcessId()
    {
        return getmypid();
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
    abstract public function getProcessUsername();

    /**
     * Returns the qualified name of the singleton class to be loaded.
     *
     * This allows an inheriting class to load a class other than itself (such as a child class)
     * if this is deemed appropriate for the executing scenario.
     *
     * @return    string
     */
    protected static function getSingletonClass()
    {
        if (self::isLinux()) {
            $qualifiedName = DLinuxServer::class;
        } else {
            $qualifiedName = DWindowsServer::class;
        }

        return $qualifiedName;
    }

    /**
     * Determines if the host server is running a Linux operating system.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $isLinux = DServer::isLinux();
     * debug($isLinux);
     * @endcode
     *
     * @return    bool
     */
    public static function isLinux()
    {
        return (PHP_OS === 'Linux');
    }

    /**
     * Determines if the host server is running a Windows operating system.
     *
     * @code
     * use app\decibel\server\DServer;
     *
     * $isWindows = DServer::isWindows();
     * debug($isWindows);
     * @endcode
     *
     * @return    bool
     */
    public static function isWindows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * Attempts to kill the process with the provided PID.
     *
     * @param    bool $force Whether to force terminiation of the process.
     *
     * @return    bool    <code>true</code> if the process was able
     *                    to be terminated, <code>false</code> otherwise.
     */
    abstract public function killProcess($pid, $force = false);
}
