<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

/**
 * Definition of the Russian language, including plural form rules.
 *
 * @author    Timothy de Paris
 */
class DRussian extends DLanguageDefinition
{
    /**
     * Returns the ISO 639-1 two letter language code by which this language
     * is represented.
     *
     * @return    string
     */
    public function getLanguageCode()
    {
        return 'ru';
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
        $mod10 = ($count % 10);
        $mod100 = ($count % 100);
        if ($this->is0thPluralForm($mod10, $mod100)) {
            $pluralForm = 0;
        } else {
            if ($this->is1stPluralForm($mod10, $mod100)) {
                $pluralForm = 1;
            } else {
                if ($this->is2ndPluralForm($mod10, $mod100)) {
                    $pluralForm = 2;
                } else {
                    $pluralForm = 3;
                }
            }
        }

        return $pluralForm;
    }

    /**
     * Checks whether this matches the rule for the 0th plural form.
     *
     * @param    int $mod10
     * @param    int $mod100
     *
     * @return    bool
     */
    protected function is0thPluralForm($mod10, $mod100)
    {
        return ($mod10 === 1 && $mod100 !== 11);
    }

    /**
     * Checks whether this matches the rule for the 1st plural form.
     *
     * @param    int $mod10
     * @param    int $mod100
     *
     * @return    bool
     */
    protected function is1stPluralForm($mod10, $mod100)
    {
        return ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14));
    }

    /**
     * Checks whether this matches the rule for the 2nd plural form.
     *
     * @param    int $mod10
     * @param    int $mod100
     *
     * @return    bool
     */
    protected function is2ndPluralForm($mod10, $mod100)
    {
        $option1 = ($mod10 === 0);
        $option2 = ($mod10 >= 5 && $mod10 <= 9);
        $option3 = ($mod100 >= 11 && $mod100 <= 14);

        return ($option1 || $option2 || $option3);
    }
}
