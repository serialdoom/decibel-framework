<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\task;

use app\decibel\authorise\auditing\DAuthenticationRecord;
use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DSessionToken;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\field\DFieldSearch;
use app\decibel\task\DRegularTask;

/**
 * Deactivates any expired logins.
 *
 * @author        Timothy de Paris
 */
class DExpireLogins extends DRegularTask
{
    /**
     * Executes the task.
     *
     * This function will be called by the task scheduler whenever the
     * task is scheduled to run.
     *
     * @return    void
     */
    protected function execute()
    {
        $expired = DSessionToken::search()
                                ->filterByField(DSessionToken::FIELD_EXPIRY, time(), DFieldSearch::OPERATOR_LESS_THAN);
        foreach ($expired as $session) {
            /* @var $session DSessionToken */
            $this->expireSession($session);
            $session->free();
        }
    }

    /**
     * Expires a session.
     *
     * @param    DSessionToken $session
     *
     * @return    void
     */
    protected function expireLogin(DSessionToken $session)
    {
        // Create the user object.
        // There is a chance the user may have been deleted while logged in.
        try {
            $user = $session->getUser();
            // Trigger logout event for the user.
            $user->logout(DAuthenticationRecord::ACTION_EXPIRED);
        } catch (DUnknownModelInstanceException $exception) {
            $user = DAuthorisationManager::getUser();
        }
        $session->delete($user);
    }

    /**
     * Returns the number of minutes that should be waited for between
     * recurring executions of this task.
     *
     * @return    int        The number of minutes.
     */
    public function getInterval()
    {
        return 1;
    }
}
