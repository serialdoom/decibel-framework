<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\model\DModel_Definition;
use app\decibel\model\field\DArrayField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DUniqueIndex;

/**
 * Decibel Group Definition.
 *
 * @author        Timothy de Paris
 */
class DGroup_Definition extends DModel_Definition
{
    /**
     * Creates a new DGroup definition.
     *
     * @param    string $qualifiedName
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Set field information.
        $name = new DTextField(DGroup::FIELD_NAME, 'Name');
        $name->setMaxLength(100);
        $name->setRequired(true);
        $this->addField($name);
        $description = new DTextField('description', 'Description');
        $description->setMaxLength(500);
        $this->addField($description);
        $privileges = new DArrayField('privileges', 'Privileges');
        $privileges->setDescription('<p>The privileges assigned to a group determine what a user assigned to that group can and can\'t access.<br />Disabled check boxes denote that the privilege is assigned to a parent of this group.</p>');
        $this->addField($privileges);
        $uniqueName = new DUniqueIndex('unique_name');
        $uniqueName->addField($name);
        $this->addIndex($uniqueName);
    }
}
