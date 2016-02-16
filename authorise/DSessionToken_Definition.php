<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\model\DLightModel;
use app\decibel\model\DLightModel_Definition;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DTextField;
use app\decibel\model\index\DUniqueIndex;

/**
 * Definition for the {@link DSessionToken} model.
 *
 * @author    Timothy de Paris
 */
class DSessionToken_Definition extends DLightModel_Definition
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
        $token = new DTextField(DSessionToken::FIELD_TOKEN, 'Token');
        $token->setMaxLength(DTextField::LENGTH_256B);
        $token->setRequired(true);
        $this->addField($token);
        $user = new DLinkedObjectField(DSessionToken::FIELD_USER, 'User');
        $user->setLinkTo(DUser::class);
        $this->addField($user);
        $expiry = new DDateTimeField(DSessionToken::FIELD_EXPIRY, 'Session Expiry');
        $expiry->setRequired(true);
        $this->addField($expiry);
        $lastUpdated = new DDateTimeField(DSessionToken::FIELD_LAST_UPDATED, 'Last Updated');
        $lastUpdated->setRequired(true);
        $this->addField($lastUpdated);
        $uniqueToken = new DUniqueIndex('unique_token');
        $uniqueToken->addField($token);
        $this->addIndex($uniqueToken);
        $this->setEventHandler(DLightModel::ON_BEFORE_SAVE, 'setLastUpdated');
    }
}
