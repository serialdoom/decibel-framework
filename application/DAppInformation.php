<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DIncorrectClassNameException;
use app\decibel\registry\DIncorrectNamespaceException;
use app\decibel\registry\DRegistryHive;

/**
 * Registers information about available Decibel Apps.
 *
 * @author        Timothy de Paris
 */
class DAppInformation extends DRegistryHive
{
    /**
     * 'App Manifest' supporting file type.
     *
     * @var        string
     */
    const FILE_MANIFEST = '.manifest.xml';
    /**
     * 'App Database Tables' supporting file type.
     *
     * @var        string
     */
    const FILE_TABLES = '.tables.xml';
    /**
     * 'App Registrations' supporting file type.
     *
     * @var        string
     */
    const FILE_REGISTRATIONS = '.info.php';
    /**
     * List of available supporting App file types.
     *
     * @var        array
     */
    private static $fileTypes = array(
        self::FILE_MANIFEST,
        self::FILE_TABLES,
        self::FILE_REGISTRATIONS,
    );
    /**
     * Index of Apps within the scope of the registry.
     *
     * @var        array
     */
    protected $apps = array();
    /**
     * Index of supporting App files within the scope of the registry.
     *
     * @var        array
     */
    protected $files = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array_merge(
            parent::generateDebug(),
            array(
                'apps'  => $this->apps,
                'files' => $this->files,
            )
        );
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'apps';
        $sleep[] = 'files';

        return $sleep;
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);

        return $fileInformation->getChecksum();
    }

    /**
     * Returns a list of Apps within the scope of this registry.
     *
     * @return    array    List of {@link DApp} objects.
     */
    public function getApps()
    {
        $apps = array();
        foreach ($this->apps as $app) {
            // Check that the class exists, just in case someone
            // manually deletes an App from the file system without
            // rebuilding the registry.
            if (class_exists($app)) {
                $apps[ $app ] = new $app();
            }
        }

        return $apps;
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(
            DFileInformation::class,
            DClassInformation::class,
        );
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
     * Returns a list of registration files for the Apps in the scope
     * of this registry.
     *
     * @return    array
     */
    public function getRegistrationFiles()
    {
        return $this->files[ self::FILE_REGISTRATIONS ];
    }

    /**
     * Returns a list of database table definition files for Apps
     * in the scope of this registry.
     *
     * @return    array
     */
    public function getTableFiles()
    {
        return $this->files[ self::FILE_TABLES ];
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
        if (!$hive instanceof DAppInformation) {
            return false;
        }
        $this->apps = array_merge(
            $this->apps,
            $hive->apps
        );
        $this->files = array_merge_recursive(
            $this->files,
            $hive->files
        );

        return true;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     * @throws    DIncorrectClassNameException
     * @throws    DIncorrectNamespaceException
     * @throws    DMultipleClassDefinitionException
     */
    protected function rebuild()
    {
        $this->apps = $this->getDependency(DClassInformation::class)
                           ->getClassNames(DApp::class);
        $this->files = [ self::FILE_MANIFEST      => [],
                         self::FILE_REGISTRATIONS => [],
                         self::FILE_TABLES        => [], ];
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);
        foreach ($fileInformation->getFiles() as $filename) {
            // Check if this is a supporting file.
            $type = substr($filename, strpos($filename, '.'));
            if (in_array($type, self::$fileTypes)) {
                $this->files[ $type ][] = $filename;
            }
        }
    }
}
