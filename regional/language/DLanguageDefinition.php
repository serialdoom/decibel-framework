<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

/**
 * Defines properties of a language, including plural form rules.
 *
 * @author    Timothy de Paris
 */
abstract class DLanguageDefinition
{
    /**
     * Returns the ISO 639-1 two letter language code by which this language
     * is represented.
     *
     * @return    string
     */
    abstract public function getLanguageCode();

    /**
     * Returns the plural form required to represent the given number of objects.
     *
     * @note
     * Returned plural form indices should be based on the order of plural forms
     * given in the unicode plural form rules, found at:
     * http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
     *
     * @param    double $count The count of objects.
     *
     * @return    int        Index of the required plural form, starting from 0.
     */
    abstract public function getPluralForm($count);
}
