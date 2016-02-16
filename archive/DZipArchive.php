<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\archive;

use app\decibel\file\DFileExistsException;
use app\decibel\file\DFileNotFoundException;
use app\decibel\file\DRecursiveFileIterator;
use ZipArchive;

/**
 * Provides a wrapper for inbuilt PHP Zip archive functionality.
 *
 * @author        Timothy de Paris
 */
class DZipArchive extends ZipArchive
{
    /**
     *
     * @param    string $filename  Filename of the archive.
     * @param    bool   $overwrite Whether to overwrite an existing file.
     *
     * @throws    DFileExistsException    If the overwrite parameter is <code>false</code>
     *                                    and a file already exists with this name.
     * @return    static
     */
    public function __construct($filename, $overwrite = false)
    {
        if (file_exists($filename)
            && !$overwrite
        ) {
            throw new DFileExistsException($filename);
        }
        // @todo handle error returned by open method
        // and throw relevant exception.
        $this->open($filename, ZipArchive::CREATE);
    }

    /**
     * Recursively adds a directory and all files to the archive.
     *
     * @param    string $path Location of the directory to add.
     *
     * @throws    DFileNotFoundException    If the specified path does not exist.
     * @return    void
     */
    public function addDirectory($path)
    {
        $iterator = DRecursiveFileIterator::getIterator($path);
        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */
            $pathname = str_replace('\\', '/', $file->getPathname());
            $relativePath = str_replace($path, '', $pathname);
            $this->addFile($pathname, $relativePath);
        }
    }
}
