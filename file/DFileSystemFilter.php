<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use SplFileInfo;

/**
 * Defines a filter to be used by the {@link DRecursiveFileSystemIterator} class.
 *
 * @author    Timothy de Paris
 */
class DFileSystemFilter
{
    /**
     * The regular expression that filenames must match.
     *
     * @var        string
     */
    protected $regex;

    /**
     * Creates a new {@link DFileSystemFilter} object.
     *
     * @return    static
     */
    public static function create()
    {
        return new DFileSystemFilter();
    }

    /**
     * Sets a regular expression that filenames must match to be included
     * in the results.
     *
     * @param    string $regex
     *
     * @return    static    This object instance, for chaining.
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;

        return $this;
    }

    /**
     * Determines if the provided file matches this filter.
     *
     * @param    SplFileInfo $file The file to match.
     *
     * @return    bool
     */
    public function match(SplFileInfo $file)
    {
        return (preg_match($this->regex, $file->getFilename()) === 1);
    }
}
