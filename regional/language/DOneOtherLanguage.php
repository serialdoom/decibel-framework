<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

/**
 * Defines plural form rules for languages that meet the 'one' and 'other'
 * standard plural forms
 *
 * @author    Timothy de Paris
 */
abstract class DOneOtherLanguage extends DLanguageDefinition
{
    /**
     * Returns the plural form required to represent the given number of objects.
     *
     * @param    double $count The count of objects.
     *
     * @return    int        Index of the required plural form, starting from 0.
     */
    public function getPluralForm($count)
    {
        // Non-strict check used as various types could be passed.
        if ($count == 1) {
            $pluralForm = 0;
        } else {
            $pluralForm = 1;
        }

        return $pluralForm;
    }
}
