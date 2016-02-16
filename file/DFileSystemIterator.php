<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use FilesystemIterator;
use FilterIterator;
use SplFileInfo;

/**
 * Defines a non-recursive iterator over the file system.
 *
 * @author    Timothy de Paris
 */
class DFileSystemIterator extends FilterIterator
{
    /**
     * The filter to apply.
     *
     * @var        DFileSystemFilter
     */
    protected $filter;

    /**
     * Creates a {@link DFileSystemIterator} object.
     *
     * @param    FilesystemIterator $iterator Filesystem iterator.
     * @param    DFileSystemFilter  $filter   Optional filter to apply.
     *
     * @return    static
     * @throws    DFileNotFoundException    If the specified path does not exist.
     */
    public function __construct(FilesystemIterator $iterator,
                                DFileSystemFilter $filter = null)
    {
        $this->filter = $filter;
        parent::__construct($iterator);
    }

    /**
     * Determines whether the currently iterated item will be included.
     *
     * @return    bool
     */
    public function accept()
    {
        if ($this->filter) {
            /* @var $file SplFileInfo */
            $file = $this->current();
            $accept = $this->filter->match($file);
        } else {
            $accept = true;
        }

        return $accept;
    }

    /**
     * Returns an iterator over the file system.
     *
     * @param    string            $path   Path to iterate.
     * @param    DFileSystemFilter $filter Optional filter to apply.
     *
     * @return    static
     * @throws    DFileNotFoundException    If the specified path does not exist.
     */
    public static function getIterator($path, DFileSystemFilter $filter = null)
    {
        if (!is_dir($path)) {
            throw new DFileNotFoundException($path);
        }

        $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO
               | FilesystemIterator::SKIP_DOTS;

        // Create an iterator over the provided path.
        $directoryIterator = new FilesystemIterator($path);
        $directoryIterator->setFlags($flags);

        return new static($directoryIterator, $filter);
    }
}
