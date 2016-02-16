<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

/**
 * Provides a fluent interface for executing linux shell processes.
 *
 * @section       versioning Version Control
 *
 * @author        David Stevens
 */
class DLinuxShell extends DShell
{
    /**
     * Sudo flag
     *
     * @var boolean
     */
    protected $sudo = false;
    /**
     * Condition types.
     *
     * @var        array
     */
    protected static $conditionTypes = array('d', 'f', 'L', 'S', 'P');

    /**
     * Generic condition for various types.
     *
     * @param    string $type
     * @param    string $path
     * @param    bool   $sudo
     *
     * @return boolean
     */
    protected static function testFileSystemCondition($type, $path, $sudo = true)
    {
        // perhaps throw exception?
        if (!in_array($type, self::$conditionTypes)) {
            return null;
        }
        $commandForCondition = "[ -{$type} {$path} ] && echo 1 || echo 0";
        if ($sudo) {
            $commandForCondition = "sudo {$commandForCondition}";
        }

        return (bool) self::create($commandForCondition)->run();
    }

    /**
     * Returns the command to be executed.
     *
     * @return    string
     */
    protected function getCommand()
    {
        // Redirect the exit code to third output pipe.
        return $this->command . ';echo $? >&3';
    }

    /**
     * Check if a directory exists using DShell and sudo.
     *
     * @param    string $path
     *
     * @return    boolean
     */
    public static function isDir($path)
    {
        return self::testFileSystemCondition('d', $path);
    }

    /**
     * Check if a file exists.
     *
     * @param    string $path
     *
     * @return    boolean
     */
    public static function isFile($path)
    {
        return self::testFileSystemCondition('f', $path);
    }

    /**
     * Check if is a symbolic link.
     *
     * @param    string $path
     *
     * @return    boolean
     */
    public static function isLink($path)
    {
        return self::testFileSystemCondition('L', $path);
    }

    /**
     * Check if is a pipe
     *
     * @param    string $path
     *
     * @return    boolean
     */
    public static function isPipe($path)
    {
        return self::testFileSystemCondition('p', $path);
    }

    /**
     * Check if is a socket.
     *
     * @param    string $path
     *
     * @return    string
     */
    public static function isSocket($path)
    {
        return self::testFileSystemCondition('S', $path);
    }
}
