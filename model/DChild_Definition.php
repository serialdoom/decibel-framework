<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model;

use app\decibel\model\DModel_Definition;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DIndex;

/**
 * Basic definition for DChild Objects
 *
 * @author    Alan Chehébar Vilas
 */
class DChild_Definition extends DModel_Definition
{
    /**
     * Creates a new DChild.
     *
     * @param    string $qualifiedName
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Set options.
        $this->setOption(DChild::OPTION_PARENT_OBJECT, false);
        // Add fields.
        $parent = new DLinkedObjectField(DChild::FIELD_PARENT, 'Parent');
        $parent->setReadOnly(true);
        $parent->setRelationalIntegrity(DLinkedObjectField::RELATIONAL_INTEGRITY_NONE);
        $parent->setExportable(false);
        $this->addField($parent);
        $parentField = new DTextField(DChild::FIELD_PARENT_FIELD, 'Parent Field');
        $parentField->setDescription("The field within the parent object that this {$this->displayName} is stored under.");
        $parentField->setMaxLength(30);
        $parentField->setReadOnly(true);
        $parentField->setExportable(false);
        $this->addField($parentField);
        $position = new DIntegerField(DChild::FIELD_POSITION, 'Position');
        $position->setDescription("The position of this {$this->displayName}.");
        $position->setExportable(false);
        $this->addField($position);
        // Optimise load of child field values.
        $loadIndex = new DIndex('index_load');
        $loadIndex->addField($parent);
        $loadIndex->addField($parentField);
        $loadIndex->addField($position);
        $this->addIndex($loadIndex);
    }
}
