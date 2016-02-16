<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\application\DApp;
use app\decibel\application\DAppInformation;
use app\decibel\file\DFileIterator;
use app\decibel\registry\DRegistryHive;
use app\decibel\stream\DFileStream;
use SplFileInfo;

/**
 * Registers information about translation files known by Decibel.
 *
 * @author    Timothy de Paris
 */
class DTranslationFileInformation extends DRegistryHive
{
    /**
     * Index of translation files within the scope of the registry.
     *
     * @var        array
     */
    protected $translationFiles = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['translationFiles'] = $this->translationFiles;

        return $debug;
    }

    ///@cond INTERNAL
    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'translationFiles';

        return $sleep;
    }
    ///@endcond
    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $appInformation DAppInformation */
        $appInformation = $this->getDependency(DAppInformation::class);

        return $appInformation->getChecksum();
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(DAppInformation::class);
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
     * Returns registered translation files for the specified language.
     *
     * @param    string $languageCode
     *
     * @return    array    List of {@link DTranslationFile} objects.
     */
    public function getTranslationFiles($languageCode)
    {
        set_default($this->translationFiles[ $languageCode ], array());
        $translationFiles = array();
        foreach ($this->translationFiles[ $languageCode ] as $filename) {
            $stream = new DFileStream(DECIBEL_PATH . $filename);
            $translationFiles[] = new DTranslationFile($stream, $languageCode);
        }

        return $translationFiles;
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool    <code>true</code> if the merge was successful,
     *                    or <code>false</code> if no merge took place.
     */
    public function merge(DRegistryHive $hive)
    {
        if ($hive instanceof DTranslationFileInformation) {
            $this->translationFiles = array_merge_recursive(
                $this->translationFiles,
                $hive->translationFiles
            );
            $merged = true;
        } else {
            $merged = false;
        }

        return $merged;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        $this->translationFiles = array();
        /* @var $appInformation DAppInformation */
        $appInformation = $this->getDependency(DAppInformation::class);
        $apps = $appInformation->getApps();
        foreach ($apps as $app) {
            /* @var $app DApp */
            $path = $app->getAbsolutePath() . '_translations/';
            if (is_dir($path)) {
                $iterator = DFileIterator::getIterator($path);
                foreach ($iterator as $file) {
                    /* @var $file SplFileInfo */
                    $relativePath = str_replace(DECIBEL_PATH, '', $file->getPathname());
                    $this->translationFiles[ $file->getFilename() ][] = $relativePath;
                }
            }
        }
    }
}
