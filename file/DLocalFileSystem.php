<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use app\decibel\file\DFileExistsException;
use app\decibel\file\DFileNotFoundException;
use SplFileInfo;

/**
 * Provides functionality for working with local files on the server.
 *
 * @author    Timothy de Paris
 */
class DLocalFileSystem
{
    /**
     * Checks that a file does not exist on the file system.
     *
     * If the file does not exists no action will be taken. If it does exist,
     * a {@link DFileExistsException} will be thrown.
     *
     * @return    void
     * @throws    DFileExistsException    If the file does exist.
     */
    protected static function checkFileDoesNotExist($filename)
    {
        if (file_exists($filename)) {
            throw new DFileExistsException($filename);
        }
    }

    /**
     * Checks that a file exists on the file system.
     *
     * If the file exists, no action will be taken. If it does not exist,
     * a {@link DFileNotFoundException} will be thrown.
     *
     * @return    void
     * @throws    DFileNotFoundException    If the file does not exist.
     */
    protected static function checkFileExists($filename)
    {
        if (!file_exists($filename)) {
            throw new DFileNotFoundException($filename);
        }
    }

    /**
     * Deletes a file and any parent directories that are empty following
     * deletion of the file.
     *
     * @param    string $pathname The file to delete.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     */
    public function cleanDelete($pathname)
    {
        $success = $this->delete($pathname);
        // Recurse up the directory hierarchy,
        // deleting empty folders.
        $parent = new SplFileInfo($pathname);
        while ($parent->getBasename() !== $parent->getPathname()
            && $this->isDirectoryEmpty($parent->getPath())) {
            $success = $success && $this->delete($parent->getPath());
            $parent = $parent->getPathInfo();
        }

        return $success;
    }

    /**
     * Copies a file from one location to another.
     *
     * @note
     * If the desination references a directory that does not currently exist,
     * this directory will be created.
     *
     * @param    string $source      The name of the existing file.
     * @param    string $destination The name of the new file.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the specified file does not exist.
     * @throws    DFileExistsException    If the destination file already exists.
     */
    public function copy($source, $destination)
    {
        static::checkFileExists($source);
        static::checkFileDoesNotExist($destination);

        // Ensure directory exists then copy the file.
        return $this->mkdirForFile($destination)
        && copy($source, $destination);
    }

    /**
     * Creates a new file and writes the provided content to it.
     *
     * @param    string $pathname     The location of the new file.
     * @param    string $contents     The contents of the new file.
     * @param    bool   $overwrite    If true, existing files with the
     *                                same name will be overwritten.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileExistsException    If the file already exists and the
     *                                    overwrite flag has not been specified.
     */
    public function createFile($pathname, $contents, $overwrite = false)
    {
        if (!$overwrite) {
            static::checkFileDoesNotExist($pathname);
        }

        // Ensure directory exists then create the file.
        return $this->mkdirForFile($pathname)
        && (bool)file_put_contents($pathname, $contents);
    }

    /**
     * Deletes the specified file or directory.
     *
     * @param    string $pathname The file or directory to delete.
     *
     * @throws    DFileNotFoundException    If the specified file or directory
     *                                    does not exist.
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     */
    public function delete($pathname)
    {
        if (!file_exists($pathname)
            && !is_link($pathname)
        ) {
            throw new DFileNotFoundException($pathname);
        }
        if (is_dir($pathname)) {
            $success = rmdir($pathname);
            // Don't check whether the file exists as on windows a link
            // to a non-existent file will not be considered to exist,
            // and therefore will not be deleted.
        } else {
            $success = unlink($pathname);
        }

        return $success;
    }

    /**
     * Delete a directory and all of its contents.
     *
     * @param    string $path The directory to delete.
     *
     * @throws    DFileNotFoundException    If the specified path does not exist.
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     */
    public function deltree($path)
    {
        $success = true;
        $iterator = DFileSystemIterator::getIterator($path);
        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */
            if ($file->isDir()) {
                $success = $success && $this->deltree($file->getPathname());
            } else {
                $success = $success && unlink($file->getPathname());
            }
        }

        return $success && rmdir($path);
    }

    /**
     * Deletes all files inside a directory, leaving the directory
     * on the filesystem.
     *
     * @note
     * Sub-directories will not be deleted by this method.
     *
     * @param    string $path The directory to empty.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the specified directory does not exist.
     */
    public function emptyDirectory($path)
    {
        // Delete each file in the directory.
        $success = true;
        $iterator = DFileIterator::getIterator($path);
        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */
            $success = $success && unlink($file->getPathname());
        }

        return $success;
    }

    /**
     * Determines if the specified file exists in the file system.
     *
     * @param    string $pathname The path of the file to check.
     *
     * @return    bool
     */
    public function fileExists($pathname)
    {
        return file_exists($pathname);
    }

    /**
     * Determines if the specified path points to a directory.
     *
     * @param    string $path The path to check.
     *
     * @return    bool
     */
    public function isDirectory($path)
    {
        return is_dir($path);
    }

    /**
     * Determines if the specified directory is empty.
     *
     * @note
     * This method considers the directory to be empty if it does not exist.
     *
     * @param    string $path The directory to check.
     *
     * @return    bool    <code>true</code> if the directory is empty,
     *                    otherwise <code>false</code>.
     * @throws    DFileNotFoundException    If the specified path does not exist.
     */
    public function isDirectoryEmpty($path)
    {
        // Iterate the directory.
        $iterator = DFileSystemIterator::getIterator($path);
        // If the iterator returns anything,
        // the directory isn't empty.
        $empty = true;
        foreach ($iterator as $file) {
            $empty = false;
            break;
        }

        return $empty;
    }

    /**
     * Creates a directory with the specified path.
     *
     * @note
     * This method will recursively create parent directories
     * if they do not exist.
     *
     * @param    string $path The path of the new directory.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileExistsException    If the directory already exists.
     */
    public function mkdir($path)
    {
        static::checkFileDoesNotExist($path);

        return mkdir(
        // Some versions of php don't like a trailing slash.
            rtrim($path, '\\/'),
            0777,
            true
        );
    }

    /**
     * Recursively creates the directory for the provided filename.
     *
     * @param    string $pathname The filename.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     */
    public function mkdirForFile($pathname)
    {
        // Extract the directory name.
        $destinationInfo = new SplFileInfo($pathname);
        $destinationDir = $destinationInfo->getPath();
        // Check if the directory already exists.
        if (is_dir($destinationDir)) {
            $success = true;
            // Otherwise create it.
        } else {
            $success = $this->mkdir($destinationDir);
        }

        return $success;
    }

    /**
     * Creates a symbolic link to a file.
     *
     * @note
     * If the desination references a directory that does not currently exist,
     * this directory will be created.
     *
     * @param    string $source      The file to link to.
     * @param    string $destination Location of the symlink
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the file to link to does not exist.
     * @throws    DFileExistsException    If the destination already exists.
     */
    public function symlink($source, $destination)
    {
        static::checkFileExists($source);
        static::checkFileDoesNotExist($destination);

        return function_exists('symlink')
        && $this->mkdirForFile($destination)
        && symlink($source, $destination);
    }

    /**
     * Creates a symbolic link to a file, or copies the file if a symlink
     * cannot be created.
     *
     * This method can be used to ensure compatability with systems that
     * may not be capable of creating symlinks.
     *
     * @note
     * If the desination references a directory that does not currently exist,
     * this directory will be created.
     *
     * @param    string $source      The file to link to.
     * @param    string $destination Location of the symlink
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the file to link to does not exist.
     * @throws    DFileExistsException    If the destination already exists.
     */
    public function symlinkOrCopy($source, $destination)
    {
        if ($this->symlink($source, $destination)) {
            $success = true;
            // If the symlink failed, try to copy.
        } else {
            $success = $this->copy($source, $destination);
        }

        return $success;
    }

    /**
     * Renames a file.
     *
     * @param    string $originalFilename The original filename.
     * @param    string $newFilename      The new filename.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the original file does not exist.
     * @throws    DFileExistsException    If a file already exists with the
     *                                    new filename.
     */
    public function rename($originalFilename, $newFilename)
    {
        static::checkFileExists($originalFilename);
        static::checkFileDoesNotExist($newFilename);

        return rename(
            $originalFilename,
            $newFilename
        );
    }

    /**
     * Recursively copies a directory, including sub-directories.
     *
     * @param    string $source           The directory to copy from.
     *                                    It is assumed that source exists
     *                                    and is a directory.
     * @param    string $destination      The directory to copy to.
     *                                    This will be created if it doesn't exist.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the source is not a valid directory.
     */
    public function xcopy($source, $destination)
    {
        $success = true;
        $iterator = DRecursiveFileIterator::getIterator($source);
        foreach ($iterator as $file) {
            /* @var $file SplFileInfo */
            $fileSource = $file->getPathname();
            $fileDestination = str_replace($source, $destination, $fileSource);
            $success = $success && $this->copy($fileSource, $fileDestination);
        }

        return $success;
    }

    /**
     * Recursively moves the contents of a directory to the specified location.
     *
     * @param    string $source      The source folder.
     * @param    string $destination The destination folder.
     *
     * @return    bool    <code>true</code> if successful,
     *                    <code>false</code> otherwise.
     * @throws    DFileNotFoundException    If the source is not a valid directory.
     */
    public function xmove($source, $destination)
    {
        return $this->xcopy($source, $destination)
        && $this->deltree($source);
    }
}
