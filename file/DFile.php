<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use app\decibel\stream\DFileStream;
use SplFileInfo;

/**
 * The {@link DFile} class provides utility functions for files.
 *
 * @author        Timothy de Paris
 */
class DFile extends SplFileInfo
{
    /**
     * List containing the number of bytes available for a range
     * of file size units.
     *
     * @var        array
     */
    private static $unitMultipliers = array(
        'kb' => 1024,
        'mb' => 1048576,
        // "M" included for compatibility with PHP ini settings.
        'm'  => 1048576,
        'gb' => 1073741824,
        'tb' => 1099511627776,
    );

    /**
     * Create a new {@link DFile} object representing the file with
     * the specified pathname.
     *
     * @note
     * A {@link DFileAccessException} will be thrown for any file that does
     * not reside within the web root for this application
     * (<code>DECIBEL_PATH</code>).
     *
     * @param    string $pathname
     *
     * @return    static
     * @throws    DFileAccessException    If the user does not have access
     *                                    to the file at the specified path.
     * @throws    DFileNotFoundException    If no file exists at the specified path.
     */
    public function __construct($pathname)
    {
        // Use PHP's realpath, but convert separators to OS default.
        $realpath = str_replace(
            array('\\', '/'),
            DIRECTORY_SEPARATOR,
            realpath($pathname)
        );

        if (!$realpath) {
            throw new DFileNotFoundException($pathname);
        }
        if (strpos($realpath, DECIBEL_PATH) !== 0) {
            throw new DFileAccessException(
                $pathname,
                'This file is located outside the web root for the application.'
            );
        }
        parent::__construct($realpath);
    }

    /**
     * Returns a human readable filesize.
     *
     * @param    int $size                The size in bytes.
     * @param    int $decimalPlaces       The number of decimal places to round to.
     *                                    Defaults to 1.
     *
     * @return    string
     */
    public static function bytesToString($size, $decimalPlaces = 1)
    {
        $sizes = array('bytes', 'kb', 'mb', 'gb', 'tb');
        $count = count($sizes);
        if ($size < 0) {
            $positive = false;
            $size = abs($size);
        } else {
            $positive = true;
        }
        $i = 0;
        while ($size >= 1024 && ($i < $count - 1)) {
            $size /= 1024;
            $i++;
        }
        // Determine string format.
        if ($i === 0 || fmod($size, 1) == 0) {
            $format = '%0.0f %s';
        } else {
            $format = '%0.' . $decimalPlaces . 'f %s';
        }

        return sprintf($format, ($positive ? $size : ($size * -1)), $sizes[ $i ]);
    }

    /**
     * Deletes the file from the file system.
     *
     * @return    bool
     */
    public function delete()
    {
        $fileSystem = new DLocalFileSystem();

        return $fileSystem->delete($this->getPathname());
    }

    /**
     * Returns a {@link DFileStream} object that can be used to read
     * and write to this file.
     *
     * @return    DFileStream
     */
    public function getStream()
    {
        return new DFileStream($this->getPathname());
    }

    /**
     * Returns a random name for a file stored in the Decibel temporary directory.
     * The filename includes the path to the temporary directory.
     *
     * @return    string
     */
    public static function getTempFilename()
    {
        $filename = tempnam(TEMP_PATH, '~');

        // Ensure consistency of slashes across operating systems.
        return str_replace('\\', '/', $filename);
    }

    /**
     * Returns the maximum upload file size in bytes, as specified by the server.
     *
     * @return    integer
     */
    public static function getMaxUploadSize()
    {
        $uploadBytes = self::stringToBytes(ini_get('upload_max_filesize'));
        $postBytes = self::stringToBytes(ini_get('post_max_size'));
        // Return the smallest value.
        if ($uploadBytes < $postBytes) {
            $maxSize = $uploadBytes;
        } else {
            $maxSize = $postBytes;
        }

        return $maxSize;
    }

    /**
     * Returns the multiplier required to convert a file size from
     * the specified unit into bytes.
     *
     * @param    string $unit The unit, for example (mb, gb, etc)
     *
     * @return    int
     */
    protected static function getUnitMultiplier($unit)
    {
        $unit = strtolower($unit);
        if (isset(self::$unitMultipliers[ $unit ])) {
            $multiplier = self::$unitMultipliers[ $unit ];
        } else {
            $multiplier = 1;
        }

        return $multiplier;
    }

    /**
     * Returns the number of bytes represented by a human readable file size.
     *
     * The string must obey the following regular expression:
     * <code>/^\\s*([0-9]+)\\s*(bytes|b|kb|mb?|gb|tb)\\s*$/i</code>
     *
     * @param    string $size The human readable filesize.
     *
     * @return    integer    The number of bytes, or null if the provided
     *                    string was invalid.
     */
    public static function stringToBytes($size)
    {
        if (is_numeric($size)) {
            $bytes = (int)$size;
        } else {
            $matches = array();
            preg_match('/^\s*([0-9.]+)\s*(bytes?|b|kb|mb?|gb|tb)\s*$/i', $size, $matches);
            if (sizeof($matches) === 0) {
                $bytes = null;
            } else {
                $value = (int)$matches[1];
                $multiplier = self::getUnitMultiplier($matches[2]);
                $bytes = ($value * $multiplier);
            }
        }

        return $bytes;
    }

    /**
     * @param string $pathname
     */
    public static function correctSlashFor($pathname)
    {
        return str_replace([ NAMESPACE_SEPARATOR, '/' ], DIRECTORY_SEPARATOR, $pathname);
    }
}
