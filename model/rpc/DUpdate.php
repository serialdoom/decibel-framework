<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\rpc;

use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DIdField;
use app\decibel\model\rpc\DModelRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Provides remote update functionality for models.
 *
 * See the @ref model_orm Developer Guide for further information about
 * interacting with model instances.
 *
 * @section        why Why Would I Use It?
 *
 * This remote procedure can be used to remotely update a model instance.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - <code>id</code>: ID of the model instance to be updated.
 * - <code>qualifiedName</code>: Optional qualified name of the model.
 *        If specified, the ID must be of an instance of the model
 *        with this qualified name.
 * - <code>commit</code>: A positive boolean value must be provided to commit
 *        updating of the model instance. If not provided, the return value
 *        of the remote procedure will indicate whether the model instance
 *        could be updated.
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
 * The user must have the correct privilege to update the model instance.
 *
 * @note
 * Other conditions may be applied depending on the model instance being updated.
 * Ability to update the model instance will ultimately be determined by the
 * {@link DBaseModel::canSave()} function.
 *
 * @subsection     example Example
 *
 * The example below will update the first and last name
 * of the <code>app\\decibel\\authorise\\DUser</code>
 * model instance with ID <code>123</code>.
 *
 * @code
 * http://application.com/remote/decibel/model/rpc/DUpdate
 *        ?qualifiedName=app\decibel\authorise\DUser
 *        &id=123
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
final class DUpdate extends DModelRemoteProcedure
{
    /**
     * 'Commit' field name.
     *
     * @var        string
     */
    const FIELD_COMMIT = 'commit';

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
        $commit = new DBooleanField(self::FIELD_COMMIT, 'Commit');
        $commit->setDefault(false);
        $this->addField($commit);
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
        // Merge any provided request data.
        $instance->mergeRequestData();
        // Determine whether to actually save the model instance or not.
        $user = $this->getUser();
        if ($this->getFieldValue(self::FIELD_COMMIT)) {
            $result = $instance->save($user);
        } else {
            $result = $instance->canSave($user);
        }
        $result->setData((string)$instance);

        return DJson::encode($result);
    }
}
