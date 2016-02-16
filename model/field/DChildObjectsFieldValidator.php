<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\model\DChild;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DResult;

/**
 * Provides functionality to validate the value
 * of a {@link app::decibel::model::field::DChildObjectsField DChildObjectsField}.
 *
 * @author        Timothy de Paris
 */
class DChildObjectsFieldValidator extends DOneToManyRelationalFieldValidator
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
        $user = DAuthorisationManager::getUser();
        $fieldName = $this->getName();
        if (isset($definable->originalData[ $fieldName ])) {
            foreach ($definable->getFieldValue($fieldName) as $child) {
                /* @var $child DChild */
                $result->merge($child->canSave($user));
            }
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
        return DChildObjectsField::class;
    }
}
