<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\rpc;

use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\rpc\DModelRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Provides remote creation functionality for models.
 *
 * See the @ref model_orm Developer Guide for further information about
 * interacting with model instances.
 *
 * @section        why Why Would I Use It?
 *
 * This remote procedure can be used to remotely create a model instance.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - <code>qualifiedName</code>: Qualified name of the model.
 * - <code>commit</code>: A positive boolean value must be provided to commit
 *        creation of the model instance. If not provided, the return value
 *        of the remote procedure will indicate whether the model instance
 *        could be created.
 *
 * @subsection     return Return Value
 *
 * - A JSON encoded {@link app::decibel::utility::DResult DResult} object, or
 * - A JSON encoded {@link app::decibel::model::DUnknownModelInstanceException DUnknownModelInstanceException}
 *   if no model instance exists with the specified ID.
 * - A JSON encoded {@link app::decibel::rpc::debug::DInvalidRpcParameterException DInvalidRpcParameterException}
 *   if an invalid value is provided for a parameter.
 * - A JSON encoded {@link app::decibel::rpc::debug::DMissingRpcParameterException DMissingRpcParameterException}
 *   if a required parameter is not provided.
 *
 * @subsection     authorisation Authorisation
 *
 * - The user must have the correct privilege to create the model instance.
 *
 * @note
 * Other conditions may be applied depending on the model instance being created.
 * Ability to create the model instance will ultimately be determined by the
 * {@link DBaseModel::canSave()} function.
 *
 * @subsection     example Example
 *
 * The example below will create a new <code>app\\decibel\\authorise\\DUser</code>
 * model instance.
 *
 * @code
 * http://application.com/remote/decibel/model/rpc/DCreate
 *        ?qualifiedName=app\decibel\authorise\DUser
 *        &firstName=John
 *        &lastName=Smith
 *        &commit=1
 * @endcode
 *
 * @note
 * The default RPC URL is configurable in %Decibel, and therefore
 * the '/remote/' component of this URL may differ on your %Decibel installation.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc models
 */
final class DCreate extends DModelRemoteProcedure
{
    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        parent::define();
        $commit = new DBooleanField('commit', 'Commit');
        $commit->setDefault(false);
        $this->addField($commit);
        // Qualified name is required for this RPC.
        $this->getField(self::FIELD_QUALIFIED_NAME)
             ->setRequired(true);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     * @throws    DUnknownModelInstanceException    If the specified instance
     *                                            does not exist.
     */
    public function execute()
    {
        // Load the model instance.
        $instance = $this->getModelInstance();
        // Merge any provided request data.
        $instance->mergeRequestData();
        // Determine whether to actually save the model instance or not.
        $user = $this->getUser();
        if ($this->getFieldValue('commit')) {
            $result = $instance->save($user);
        } else {
            $result = $instance->canSave($user);
        }

        return DJson::encode($result);
    }
}
