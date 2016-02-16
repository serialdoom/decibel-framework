<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\decorator\DDecorator;
use app\decibel\file\DFileIterator;
use app\decibel\file\DFileNotFoundException;
use app\decibel\regional\DTranslationFile;
use app\decibel\stream\DFileStream;
use SplFileInfo;

/**
 * Locates translation files for an App.
 *
 * @author    Timothy de Paris
 */
class DAppTranslationFileLocator extends DDecorator
{
    /**
     * Determines the location of translation files for the App
     * with the provided qualified name.
     *
     * @param    string $qualifiedName
     *
     * @return    string
     */
    protected function getFileLocation($qualifiedName)
    {
        return DECIBEL_PATH
        . dirname(str_replace('\\', '/', $qualifiedName))
        . '/_translations/';
    }

    /**
     * Returns a list of translation files available for this App.
     *
     * @return    array    List of {@link DTranslationFile} objects.
     */
    public function getTranslationFiles()
    {
        $translationFiles = array();
        // Iterate through the theme hierarchy to locate all
        // provided translation files.
        $qualifiedName = get_class($this->getDecorated());
        while ($qualifiedName) {
            $path = $this->getFileLocation($qualifiedName);
            $translationFiles = array_merge(
                $translationFiles,
                $this->getFromPath($path)
            );
            $qualifiedName = get_parent_class($qualifiedName);
        }

        return $translationFiles;
    }

    /**
     * Returns a list of translation files available at the specified path.
     *
     * @param    string $path
     *
     * @return    array    List of {@link DTranslationFile} objects.
     */
    protected function getFromPath($path)
    {
        $translationFiles = array();
        try {
            $iterator = DFileIterator::getIterator($path);
            foreach ($iterator as $file) {
                /* @var $file SplFileInfo */
                $stream = new DFileStream($file->getPathname());
                $translationFiles[] = new DTranslationFile($stream, $file->getFilename());
            }
        } catch (DFileNotFoundException $exception) {
        }

        return $translationFiles;
    }

    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @note
     * This method must be overriden by implementing classes.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DApp::class;
    }
}
