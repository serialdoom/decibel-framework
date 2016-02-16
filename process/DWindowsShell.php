<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DShell;

/**
 * Provides a fluent interface for executing windows shell processes.
 *
 * The empty methods reflect the DLinuxShell methods.
 * Needs translating into windows speak.
 *
 * @author        David Stevens
 */
class DWindowsShell extends DShell
{
    /**
     * Generic condition for various types.
     *
     * @param    string $type The type of condition
     * @param    string $path The condition path
     * @param    string $sudo The sudo flag
     *
     * @return boolean
     */
    public static function condition($type, $path, $sudo = true)
    {
        return false;
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
        return false;
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
        return false;
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
        return false;
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
        return false;
    }

    /**
     * Check if is a socket.
     *
     * @param type $path
     *
     * @return type
     */
    public static function isSocket($path)
    {
        return false;
    }
}
