<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\utility\DDefinable;
use app\decibel\utility\DResult;

/**
 * Provides functionality to validate the value
 * of a {@link app::decibel::model::field::DOneToManyRelationalField DOneToManyRelationalField}.
 *
 * @author        Timothy de Paris
 */
class DOneToManyRelationalFieldValidator extends DFieldValidator
{
    /**
     * Performs any specific validation of the provided
     * data required by this field.
     *
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     *
     * @return    DResult
     */
    protected function checkValue($data, DDefinable $definable = null)
    {
        $result = parent::checkValue($data, $definable);
        $count = count($data);
        $minLinks = $this->getMinLinks();
        $maxLinks = $this->getMaxLinks();
        // Check minimum requirement.
        if ($count < $minLinks) {
            $result->setSuccess(false,
                                "#fieldName# reqiures a minimum of {$minLinks} {$this->getLinkDisplayNamePlural()}");
        }
        // Check maximum requirement.
        if ($maxLinks
            && $count > $maxLinks
        ) {
            $result->setSuccess(false,
                                "#fieldName# can have no more than {$maxLinks} {$this->getLinkDisplayNamePlural()}");
        }

        return $result;
    }

    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DOneToManyRelationalField::class;
    }
}
