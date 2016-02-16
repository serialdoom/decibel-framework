<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

/**
 * Definition of the Chinese language, including plural form rules.
 *
 * @author    Timothy de Paris
 */
class DChinese extends DLanguageDefinition
{
    /**
     * Returns the ISO 639-1 two letter language code by which this language
     * is represented.
     *
     * @return    string
     */
    public function getLanguageCode()
    {
        return 'zh';
    }

    /**
     * Returns the plural form required to represent the given number of objects.
     *
     * @param    double $count The count of objects.
     *
     * @return    int        Index of the required plural form, starting from 0.
     */
    public function getPluralForm($count)
    {
        return 0;
    }
}
