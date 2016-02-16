<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

/**
 * Allows a class to provide translations for multi-lingual functionality.
 *
 * @author    Timothy de Paris
 */
interface DTranslationProvider
{
    /**
     * Returns a list of provided translation files.
     *
     * @return    array    List of {@link DTranslationFile} objects.
     */
    public function getTranslationFiles();

    /**
     * Returns the scope of the provided translations.
     *
     * @return    string    Qualified name of a class implementing {@link DTranslationScope}
     */
    public function getTranslationScope();
}
