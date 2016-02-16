<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\authorise\DUser;
use app\decibel\model\field\DQualifiedNameField;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Returns information about a {@link DScheduledTask} or {@link DQueueableTask}.
 *
 * See the @ref tasks Developer Guide for further information about tasks.
 *
 * @section        why Why Would I Use It?
 *
 * This procedure can be used to determine if a specific task is currently
 * running, or if and when it is scheduled to run.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * - <code>task</code>: Qualified name of the task to retrieve the status of.
 *
 * @subsection     return Return Value
 *
 * - A JSON encoded {@link app::decibel::task::DTaskStatus DTaskStatus} object, or
 * - A JSON encoded {@link app::decibel::rpc::debug::DMissingRpcParameterException DMissingRpcParameterException}
 *        if no <code>task</code> was provided, or
 * - A JSON encoded {@link app::decibel::rpc::debug::DInvalidRpcParameterException DInvalidRpcParameterException}
 *        if an invalid <code>task</code> was provided.
 *
 * @subsection     authorisation Authorisation
 *
 * - The <code>app\\decibel\\maintenance-General</code> privilege is required
 * to use this remote procedure.
 *
 * @subsection     example Example
 *
 * The example below will retrieve the status of the
 * {@link app::decibel::authorise::task::DExpireLogins DExpireLogins} task.
 *
 * @code
 * http://application.com/remote/decibel/task/DGetTaskStatus
 *        ?task=app\decibel\authorise\task\DExpireLogins
 * @endcode
 *
 * @note
 * The default RPC URL is configurable in %Decibel, and therefore
 * the '/remote/' component of this URL may differ on your %Decibel installation.
 *
 * @subsubsection  example_return Sample Return Value
 *
 * @code
 * {
 *    "name":"app\\decibel\\authorise\\task\\DExpireLogins",
 *    "type":"app\\decibel\\task\\DRegularTask",
 *    "scheduledTime":1365065316,
 *    "pending":true,
 *    "initiating":false
 *    "running":false
 *    "progress":0,
 *    "processId":null,
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_rpc
 */
final class DGetTaskStatus extends DRemoteProcedure
{
    /**
     * 'Task' field name.
     *
     * @var        string
     */
    const FIELD_TASK = 'task';

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
        return $user->hasPrivilege('app\\decibel\\maintenance-General');
    }

    /**
     * Defines the parameters available for this remote procedure.
     *
     * @return    void
     */
    protected function define()
    {
        $task = new DQualifiedNameField(self::FIELD_TASK, 'Task');
        $task->setAncestors(array(DTask::class));
        $task->setRequired(true);
        $this->addField($task);
    }

    /**
     * Executes the remote procedure and returns the result.
     *
     * @return    string        Result of the procedure.
     */
    public function execute()
    {
        $task = $this->getFieldValue(self::FIELD_TASK);

        return DJson::encode(
            $task::getStatus()
        );
    }

    /**
     * Determines whether profiling can occur on this remote procedure.
     *
     * By default, profiling is enabled on remote procedures.
     *
     * @return    bool
     */
    public function profile()
    {
        return false;
    }
}
