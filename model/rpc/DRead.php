<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\rpc;

use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\field\DIdField;
use app\decibel\model\rpc\DModelRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Provides remote viewing functionality for models.
 *
 * See the @ref model_orm Developer Guide for further information about
 * interacting with model instances.
 *
 * @section        why Why Would I Use It?
 *
 * This remote procedure can be used to remotely view a model instance.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - <code>id</code>: ID of the model instance to be viewed.
 * - <code>qualifiedName</code>: Optional qualified name of the model.
 *        If specified, the ID must be of an instance of the model
 *        with this qualified name.
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
 * http://application.com/remote/decibel/model/rpc/DRead
 *        ?qualifiedName=app\decibel\authorise\DUser
 *        &id=123
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
final class DRead extends DModelRemoteProcedure
{
    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        parent::define();
        $id = new DIdField('id', 'ID');
        $id->setRequired(true);
        $this->addField($id);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @param    DRequest $request Parameters passed to the procedure.
     *
     * @return    string        Result of the procedure.
     * @throws    DUnknownModelInstanceException    If the specified instance
     *                                            does not exist.
     */
    public function execute()
    {
        // Load the model instance.
        $instance = $this->getModelInstance();

        return DJson::encode($instance);
    }
}
