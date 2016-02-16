<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model;

use app\decibel\authorise\DUser;
use app\decibel\debug\DInvalidMethodCallException;
use app\decibel\model\DBaseModel_Definition;
use app\decibel\model\debug\DDuplicateFieldNameException;
use app\decibel\model\debug\DUnsupportedFieldTypeException;
use app\decibel\model\DModel;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DField;
use app\decibel\model\field\DIdField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DIndex;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\regional\DLabel;

/**
 * Provides a base definition for objects that will form the Model for a Decibel application.
 *
 * @author        Nikolay Dimitrov
 */
class DModel_Definition extends DBaseModel_Definition
{
    /**
     * Constructs an instance of this class
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DModel_Definition
     */
    protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $id = new DIdField('id', 'ID');
        $this->addField($id);
        $qualifiedNameField = new DTextField(DModel::FIELD_QUALIFIED_NAME, 'Qualified Name');
        $qualifiedNameField->setMaxLength(100);
        $qualifiedNameField->setExportable(false);
        $qualifiedNameField->setRandomisable(false);
        $this->addField($qualifiedNameField);
        $guid = new DTextField(DModel::FIELD_GUID, 'GUID');
        $guid->setReadOnly(true);
        $guid->setMaxLength(32);
        $guid->setRandomisable(false);
        $this->addField($guid);
        // Handle ID and version fields and primary key.
        if (get_called_class() === __CLASS__) {
            // ID field is autoincrementing in the DModel table.
            $id->setAutoincrement(true);
            $guidIndex = new DIndex('index_guid');
            $guidIndex->addField($guid);
            $this->addIndex($guidIndex);
            $qualifiedNameIndex = new DIndex('index_qualifiedName');
            $qualifiedNameIndex->addField($qualifiedNameField);
            $this->addIndex($qualifiedNameIndex);
        } else {
            // Ensure the ID field is registered in the table of the defined
            // object (it isn't a shared field, but must be present in all models).
            $id->setModelInformation($qualifiedName);
        }
        $created = new DDateTimeField(DModel::FIELD_CREATED, new DLabel(DModel::class, DModel::FIELD_CREATED));
        $created->setDescription(new DLabel(DModel::class, 'createdDescription',
                                            array('displayName' => $this->displayName)));
        $created->setReadOnly(true);
        $created->setRandomisable(false);
        $this->addField($created);
        $lastUpdated = new DDateTimeField(DModel::FIELD_LAST_UPDATED,
                                          new DLabel(DModel::class, DModel::FIELD_LAST_UPDATED));
        $lastUpdated->setDescription("The date and time at which this {$this->displayName} was last modified.");
        $lastUpdated->setReadOnly(true);
        $lastUpdated->setRandomisable(false);
        $this->addField($lastUpdated);
        $field = new DLinkedObjectField(DModel::FIELD_CREATED_BY, new DLabel(DModel::class, 'createdBy'));
        $field->setNullOption('Unknown');
        $field->setLinkTo(DUser::class);
        $field->setRelationalIntegrity(DLinkedObjectField::RELATIONAL_INTEGRITY_NONE);
        $field->setReadOnly(true);
        $field->setRandomisable(false);
        $this->addField($field);
        $lastUpdatedBy = new DLinkedObjectField(DModel::FIELD_LAST_UPDATED_BY,
                                                new DLabel(DModel::class, 'lastUpdatedBy'));
        $lastUpdatedBy->setNullOption('Unknown');
        $lastUpdatedBy->setLinkTo(DUser::class);
        $lastUpdatedBy->setRelationalIntegrity(DLinkedObjectField::RELATIONAL_INTEGRITY_NONE);
        $lastUpdatedBy->setReadOnly(true);
        $lastUpdatedBy->setRandomisable(false);
        $this->addField($lastUpdatedBy);
        $stringValue = new DTextField(DModel::FIELD_STRING_VALUE, new DLabel(DModel::class, 'name'));
        $stringValue->setReadOnly(true);
        $stringValue->setExportable(false);
        $stringValue->setRandomisable(false);
        $this->addField($stringValue);
        // Indexes
        $primaryKey = new DPrimaryIndex();
        $primaryKey->addField($id);
        $this->addIndex($primaryKey);
        // Set event handlers.
        $this->setEventHandler(DModel::ON_BEFORE_FIRST_SAVE, 'setDefaultValues');
        $this->setEventHandler(DModel::ON_BEFORE_DELETE, 'checkUtilisation');
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
        // XXX: does not work when symlinking files
        // $qualifiedName = str_replace('/', '\\',
        //                              substr(debug_backtrace()[0]['file'], strlen(DECIBEL_PATH), -strlen('_Definition.php')));

        $qualifiedName = substr(debug_backtrace()[1]['class'], 0, -strlen('_Definition'));
        $field->setModelInformation($this->qualifiedName, $qualifiedName);

        // Pass field to parent to finish the process.
        parent::addField($field);
    }
}
