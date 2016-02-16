<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;

/**
 * Defines an iterator over the file system.
 *
 * @author    Timothy de Paris
 */
class DRecursiveFileSystemIterator extends RecursiveFilterIterator
{
    /**
     * The filter to apply.
     *
     * @var        DFileSystemFilter
     */
    protected $filter;

    /**
     * Creates a {@link DRecursiveFileSystemIterator} object.
     *
     * @param    RecursiveIterator $iterator The iterator.
     * @param    DFileSystemFilter $filter   Optional filter to apply.
     *
     * @return    static
     */
    public function __construct(RecursiveIterator $iterator,
                                DFileSystemFilter $filter = null)
    {
        parent::__construct($iterator);
        $this->filter = $filter;
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
     *
     * @return    static
     */
    public function getChildren()
    {
        return new static(
            $this->getInnerIterator()->getChildren(),
            $this->filter
        );
    }

    /**
     * Returns a recursive iterator over the file system.
     *
     * @param    string            $path   Path to iterate.
     * @param    DFileSystemFilter $filter Optional filter to apply.
     *
     * @return    RecursiveIteratorIterator
     * @throws    DFileNotFoundException    If the specified path does not exist.
     */
    public static function getIterator($path, DFileSystemFilter $filter = null)
    {
        if (!is_dir($path)) {
            throw new DFileNotFoundException($path);
        }

        $flags = RecursiveDirectoryIterator::KEY_AS_PATHNAME | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
               | RecursiveDirectoryIterator::SKIP_DOTS;

        // Create an iterator over the provided path.
        $directoryIterator = new RecursiveDirectoryIterator($path);
        $directoryIterator->setFlags($flags);

        $filterIterator = new static($directoryIterator, $filter);
        return new RecursiveIteratorIterator(
            $filterIterator,
            static::getRecursiveIteratorMode()
        );
    }

    /**
     * Returns the mode in which a RecursiveIteratorIterator should behave
     * when applied to this file system iterator.
     *
     * @return    int
     */
    public static function getRecursiveIteratorMode()
    {
        return RecursiveIteratorIterator::SELF_FIRST;
    }
}
