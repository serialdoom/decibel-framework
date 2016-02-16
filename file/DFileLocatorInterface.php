<?php
namespace app\decibel\file;

interface DFileLocatorInterface
{
    /**
     * Returns a full path for a given file name.
     *
     * @param string      $name        The file name to locate
     * @param string|null $currentPath The current path
     * @param bool        $first       Whether to return the first occurrence or an array of filenames
     *
     * @return string|array The full path to the file or an array of file paths
     *
     * @throws \InvalidArgumentException When file is not found
     */
    public function locate($name, $currentPath = null, $first = true);
}
