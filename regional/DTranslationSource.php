<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

/**
 * Defines a class that provides translations.
 *
 * @author    Timothy de Paris
 */
abstract class DTranslationSource
{
    /**
     * Language for which this file provides translations.
     *
     * @var        string
     */
    protected $languageCode;

    /**
     * Creates a {@link DTranslationSource}.
     *
     * @param    string $languageCode Language for which this file provides translations.
     *
     * @return    static
     */
    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * Returns a list of defined labels.
     *
     * @return    array    List of labels with namespaced label names as keys.
     */
    abstract public function getLabels();

    /**
     * Returns the language for which this file provides translations.
     *
     * @return    string
     */
    public function getLanguage()
    {
        return $this->languageCode;
    }
}
