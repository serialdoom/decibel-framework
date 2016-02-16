<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use SplFileInfo;

/**
 * Defines a non-recursive iterator over directories on the file system.
 *
 * @author    Timothy de Paris
 */
class DDirectoryIterator extends DFileSystemIterator
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
