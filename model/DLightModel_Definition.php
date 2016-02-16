<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\debug\DInvalidMethodCallException;
use app\decibel\model\DBaseModel_Definition;
use app\decibel\model\debug\DDuplicateFieldNameException;
use app\decibel\model\debug\DUnsupportedFieldTypeException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DIdField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\regional\DLabel;
use app\decibel\registry\DInvalidClassInheritanceException;

/**
 * Defines the base definition class for light models.
 *
 * @author        Timothy de Paris
 */
abstract class DLightModel_Definition extends DBaseModel_Definition
{
    /**
     * Constructs an instance of this class
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DLightModel_Definition
     * @throws    DInvalidClassInheritanceException    If the model for this definition
     *                                                inherits from an invalid class.
     */
    protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Add fields to this definiton
        $id = new DIdField('id', 'ID');
        $id->setAutoincrement(true);
        $this->addField($id);
        $stringValue = new DTextField(DLightModel::FIELD_STRING_VALUE, new DLabel(DModel::class, 'name'));
        $stringValue->setExportable(false);
        $stringValue->setRandomisable(false);
        $this->addField($stringValue);
        // Add primary key index.
        // This will be added to inheriting class tables, not the DModel table.
        $index = new DPrimaryIndex();
        $index->addField($id);
        $this->addIndex($index);
    }

    /**
     * Adds a field to this object definition.
     *
     * @param    DField $field Definition of the field to add.
     *
     * @return    void
     * @throws    DDuplicateFieldNameException    If a field with this name
     *                                            has already been registered.
     * @throws    DUnsupportedFieldTypeException    If the field is not able to
     *                                            be added to this definition.
     * @throws    DInvalidMethodCallException        If this method is called before
     *                                            {@link DModel_Definition::__construct()}.
     */
    public function addField(DField $field)
    {
        if (!$field->isNativeField()) {
            throw new DUnsupportedFieldTypeException($field, $this);
        }
        // Set the object information.
        $field->setModelInformation($this->qualifiedName);
        // Pass field to parent to finish the process.
        parent::addField($field);
    }
}
