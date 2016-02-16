<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\action;

use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;

/**
 * Provides functionality to randomise the field values of {@link DBaseModel} instances.
 *
 * @author    Timothy de Paris
 */
class DBaseModelRandomiser extends DRuntimeDecorator
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DBaseModel::class;
    }

    /**
     * Randomises the content of this model.
     *
     * Content is generated by the {@link app::decibel::model::field::DField::getRandomValue()
     * DField::getRandomValue()} function.
     *
     * @return    void
     */
    public function randomiseContent()
    {
        // Randomise content for each available field.
        foreach ($this->getFields() as $field) {
            /* @var $field DField */
            if ($field->isRandomisable()) {
                $this->randomiseField($field);
            }
        }
    }

    /**
     * Randomises content for the provided field.
     *
     * @param    DField $field
     *
     * @return    void
     * @since    6.8.
     */
    protected function randomiseField(DField $field)
    {
        $value = $field->getRandomValue();
        if ($value !== null) {
            $fieldName = $field->getName();
            $this->setFieldValue($fieldName, $value);
        }
    }
}
