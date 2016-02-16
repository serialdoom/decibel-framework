<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\task;

use app\decibel\authorise\DUser;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;

/**
 * Returns information about currently scheduled tasks.
 *
 * See the @ref tasks Developer Guide for further information about tasks.
 *
 * @section        why Why Would I Use It?
 *
 * This procedure can be used to determine which tasks are scheduled
 * or currently being executed.
 *
 * @section        how How Do I Use It?
 *
 * The remote procedure can be accessed via an AJAX or cURL request.
 * See @ref rpc_executing for more information.
 *
 * @subsection     parameters Parameters
 *
 * There are no parameters available for this remote procedure.
 *
 * @subsection     return Return Value
 *
 * - A JSON encoded list of {@link app::decibel::task::DTaskStatus DTaskStatus}
 *        objects will be returned.
 *
 * @subsection     authorisation Authorisation
 *
 * - The <code>app\\decibel\\maintenance-General</code> privilege
 *        is required to use this remote procedure.
 *
 * @subsection     example Example
 *
 * The example below will retrieve the task schedule.
 *
 * @code
 * http://application.com/remote/decibel/task/DGetSchedule
 * @endcode
 *
 * @note
 * The default RPC URL is configurable in %Decibel, and therefore
 * the '/remote/' component of this URL may differ on your %Decibel installation.
 *
 * @subsubsection  example_return Sample Return Value
 *
 * @code
 * [
 *    {
 *        "task":"app\\decibel\\authorise\\task\\DExpireLogins",
 *        "type":"app\\decibel\\task\\DNightlyTask",
 *        "scheduledTime":1365324532,
 *        "pending":true,
 *        "initiating":false,
 *        "running":false,
 *        "progress":false,
 *        "processId":null
 *    },
 *    {
 *        "task":"app\\decibel\\database\\maintenance\\DOptimiseDatabase",
 *        "type":"app\\decibel\\task\\DScheduledTask",
 *        "scheduledTime":1365264562,
 *        "pending":false,
 *        "initiating":false,
 *        "running":true,
 *        "progress":30,
 *        "processId":3245
 *    }
 * ]
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        tasks_rpc
 */
final class DGetSchedule extends DRemoteProcedure
{
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
     * Executes the remote procedure and returns the result.
     *
     * @param    DRequest $request Parameters passed to the procedure.
     *
     * @return    string        Result of the procedure.
     */
    public function execute()
    {
        return DJson::encode(
            DTaskSchedule::getSchedule()
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
