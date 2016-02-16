<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\authorise\DProfile;
use app\decibel\model\DModel_Definition;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DLinkedObjectsField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DIndex;
use app\decibel\model\index\DUniqueIndex;

/**
 * Decibel Profile Definition.
 *
 * @author        Timothy de Paris
 */
class DProfile_Definition extends DModel_Definition
{
    /**
     * Creates a new DProfile definition.
     *
     * @param    string $qualifiedName
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Set field information.
        $name = new DTextField('name', 'Name');
        $name->setMaxLength(30);
        $name->setRequired(true);
        $this->addField($name);
        $description = new DTextField('description', 'Description');
        $description->setMaxLength(1000);
        $this->addField($description);
        $userObject = new DEnumStringField(DProfile::FIELD_USER_OBJECT, 'User Type');
        $userObject->setValues($qualifiedName::getUserClasses());
        $userObject->setMaxLength(100);
        $this->addField($userObject);
        $groups = new DLinkedObjectsField(DProfile::FIELD_GROUPS, 'Default Groups');
        $groups->setDescription('Any user assigned this profile will be added to these groups by default.');
        $groups->setLinkTo(DGroup::class);
        $this->addField($groups);
        // Register indexes.
        $uniqueName = new DUniqueIndex('unique_name');
        $uniqueName->addField($name);
        $this->addIndex($uniqueName);
        $index = new DIndex('index_userObject');
        $index->addField($userObject);
        $this->addIndex($index);
    }
}
