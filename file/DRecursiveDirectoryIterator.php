<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use SplFileInfo;

/**
 * Defines a recursive iterator over directories on the file system.
 *
 * @author    Timothy de Paris
 */
class DRecursiveDirectoryIterator extends DRecursiveFileSystemIterator
{
    /**
     * Determines whether the currently iterated item will be included.
     *
     * @return    bool
     */
    public function accept()
    {
        /* @var $file SplFileInfo */
        $file = $this->current();
        if ($file->isDir()) {
            $accept = parent::accept();
        } else {
            $accept = false;
        }

        return $accept;
    }
}
