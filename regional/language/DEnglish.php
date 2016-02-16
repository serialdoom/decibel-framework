<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

/**
 * Definition of the English language, including plural form rules.
 *
 * @author    Timothy de Paris
 */
class DEnglish extends DOneOtherLanguage
{
    /**
     * Returns the ISO 639-1 two letter language code by which this language
     * is represented.
     *
     * @return    string
     */
    public function getLanguageCode()
    {
        return 'en';
    }
}
