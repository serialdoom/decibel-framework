<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\file\DFileIterator;
use app\decibel\file\DFileSystemFilter;
use app\decibel\file\DRecursiveDirectoryIterator;
use SplFileInfo;

/**
 * Registers information about the files available within a Decibel App.
 *
 * @author        Timothy de Paris
 */
class DFileInformation extends DRegistryHive
{
    /**
     * Index of files included within the scope of the registry hive.
     *
     * @var        array
     */
    protected $files = array();

    /**
     * List of indexed files, where indexing is performed.
     *
     * @var        array
     */
    private $indexedFiles;

    /**
     * The latest modification date of files in the indexed file list.
     *
     * @var        array
     */
    private $indexLastUpdated;

    /**
     * The last updated timestamp of the last updated file within the App.
     *
     * @var        int
     */
    protected $lastUpdated;

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['files'] = $this->files;
        $debug['lastUpdated'] = $this->lastUpdated;

        return $debug;
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'files';
        $sleep[] = 'lastUpdated';

        return $sleep;
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        // Rebuild the file index if this hasn't already been done.
        if ($this->indexedFiles === null) {
            $this->indexFiles();
        }
        // Checksum is built from the serialized file list
        // and the timestamp of the last updated file.
        return md5(serialize($this->indexedFiles) . $this->indexLastUpdated);
    }

    /**
     * Returns the files indexed by this registry hive.
     *
     * @return    array
     */
    public function &getFiles()
    {
        return $this->files;
    }

    /**
     * Returns a version number indicating the format of the registry.
     *
     * @return    int
     */
    public function getFormatVersion()
    {
        return 1;
    }

    /**
     * Non-recursively indexes the contents of a directory.
     *
     * @param    SplFileInfo $directory Information about the directory.
     *
     * @return    void
     */
    protected function indexDirectory(SplFileInfo $directory)
    {
        // Ignore files not starting with an upper-case letter.
        $fileFilter = DFileSystemFilter::create()
                                       ->setRegex('/^[A-Z]/');
        $fileIterator = DFileIterator::getIterator(
            $directory->getPathname(),
            $fileFilter
        );
        foreach ($fileIterator as $file) {
            /* @var $file SplFileInfo */
            $this->indexFile(
                $file,
                $this->indexedFiles,
                $this->indexLastUpdated
            );
        }
    }

    /**
     * Adds a file to the index for this registry.
     *
     * @param    SplFileInfo $file            Information about the file.
     * @param    array       $files           Pointer to the indexed files.
     * @param    int         $lastUpdated     The most recently updated file
     *                                        so far indexed.
     *
     * @return    bool    <code>true</code> if the file was added to the
     *                    list of files, <code>false</code> if not.
     */
    protected function indexFile(SplFileInfo $file,
                                 array &$files, &$lastUpdated)
    {
        // Ignore registry files!
        if ($file->getExtension() === 'registry') {
            return false;
        }
        // Check if this is the most recently modified file so far.
        $modified = $file->getMTime();
        if ($modified > $lastUpdated) {
            $lastUpdated = $modified;
        }
        // Add to the list of files.
        $relativePath = str_replace(DECIBEL_PATH, '', $file->getPathname());
        $files[] = $relativePath;

        return true;
    }

    /**
     * Index the file system and store the list of files
     * and last modified time.
     *
     * @return    void
     */
    private function indexFiles()
    {
        $this->indexedFiles = array();
        // Add files from the root directory.
        $directory = new SplFileInfo($this->registry->getAbsolutePath());
        $this->indexDirectory($directory);
        // Ignore directories starting with an underscore
        // or with a dot (for example, .svn directories).
        $directoryFilter = DFileSystemFilter::create()
                                            ->setRegex('/^[^_.]/');
        $directoryIterator = DRecursiveDirectoryIterator::getIterator(
            $directory->getPathname(),
            $directoryFilter
        );
        foreach ($directoryIterator as $directory) {
            /* @var $directory SplFileInfo */
            $this->indexDirectory($directory);
        }
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool
     */
    public function merge(DRegistryHive $hive)
    {
        if (!$hive instanceof DFileInformation) {
            return false;
        }
        $this->files = array_merge(
            $this->files,
            $hive->files
        );
        if ($hive->lastUpdated > $this->lastUpdated) {
            $this->lastUpdated = $hive->lastUpdated;
        }

        return true;
    }

    /**
     * indexedFiles cannot be emptied without this clear,
     * this can however be useful (mainly for testing)
     *
     * Clear is immutable to prevent disruption of $files
     */
    public function clear()
    {
        $new = clone $this;
        $new->indexedFiles = null;
        $new->files = array();
        return $new;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        // Rebuild the file index if this hasn't already been done.
        if ($this->indexedFiles === null) {
            $this->indexFiles();
        }
        $this->files =& $this->indexedFiles;
        $this->lastUpdated = $this->indexLastUpdated;
    }
}
