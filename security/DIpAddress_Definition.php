<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\model\DLightModel_Definition;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DUniqueIndex;
use app\decibel\security\DIpAddress;
use app\decibel\validator\DIpValidator;

/**
 * Definition for the DIpAddress model.
 *
 * @author    Andrzej Kus
 */
class DIpAddress_Definition extends DLightModel_Definition
{
    /**
     * Creates the definition.
     *
     * @param    string $qualifiedName
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $ipAddress = new DTextField(DIpAddress::FIELD_IP_ADDRESS, 'IP Address');
        $ipAddress->setMaxLength(15);
        $ipAddress->setRequired(true);
        $ipAddress->addValidationRule(new DIpValidator());
        $this->addField($ipAddress);
        $flag = new DEnumField(DIpAddress::FIELD_FLAG, 'Flag');
        $flag->setValues(DIpAddress::getFlagOptions());
        $flag->setDefault(DIpAddress::FLAG_KNOWN);
        $flag->setRequired(true);
        $this->addField($flag);
        $description = new DTextField(DIpAddress::FIELD_DESCRIPTION, 'Description');
        $description->setMaxLength(250);
        $this->addField($description);
        $uniqueIpAddress = new DUniqueIndex('unique_ipAddress');
        $uniqueIpAddress->addField($ipAddress);
        $this->addIndex($uniqueIpAddress);
        $this->setEventHandler(DIpAddress::ON_BEFORE_SAVE, 'validateIpAddress');
        $this->setEventHandler(DIpAddress::ON_DELETE, 'clearIpAddressCache');
    }
}
