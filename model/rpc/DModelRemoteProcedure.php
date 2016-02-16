<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\rpc;

use app\decibel\authorise\DGuestUser;
use app\decibel\authorise\DUser;
use app\decibel\model\DBaseModel;
use app\decibel\model\DModel;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\rpc\DRemoteProcedure;

/**
 * Base class for remote procedures that can be used to perform actions
 * on models.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc models
 */
abstract class DModelRemoteProcedure extends DRemoteProcedure
{
    /**
     * 'Qualified Name' field name.
     *
     * @var        string
     */
    const FIELD_QUALIFIED_NAME = 'qualifiedName';

    /**
     * Determines if the specified user is authorised to execute
     * this remote procedure call.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool        true if the user is able to execute the procedure,
     *                        false otherwise.
     */
    public function authorise(DUser $user)
    {
        // Require a user to be logged in.
        return !$user instanceof DGuestUser;
    }

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $qualifiedName = new DQualifiedNameField(self::FIELD_QUALIFIED_NAME, 'Qualified Name');
        $qualifiedName->setAncestors(array(DBaseModel::class));
        $this->addField($qualifiedName);
    }

    /**
     * Returns the model instance on which the remote procedure
     * will perform an action.
     *
     * @return    DBaseModel
     * @throws    DUnknownModelInstanceException    If the specified instance
     *                                            does not exist.
     */
    public function getModelInstance()
    {
        $qualifiedName = $this->getFieldValue(self::FIELD_QUALIFIED_NAME);
        $id = (int)$this->getFieldValue('id');
        if ($qualifiedName) {
            $instance = $qualifiedName::create($id);
            // Load based on ID only.
        } else {
            $instance = DModel::create($id);
        }
        // Check the model instance was successfully loaded.
        if (!$instance) {
            throw new DUnknownModelInstanceException($id, $qualifiedName);
        }

        return $instance;
    }
}
