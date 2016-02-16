<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use RecursiveIteratorIterator;

/**
 * Defines a recursive iterator over files on the file system.
 *
 * @author    Timothy de Paris
 */
class DRecursiveFileIterator extends DRecursiveFileSystemIterator
{
    /**
     * Returns the mode in which a RecursiveIteratorIterator should behave
     * when applied to this file system iterator.
     *
     * @return    int
     */
    public static function getRecursiveIteratorMode()
    {
        return RecursiveIteratorIterator::LEAVES_ONLY;
    }
}
