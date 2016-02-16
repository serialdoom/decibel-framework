<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\authorise\DUser;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Manages the process of authorising a user.
 *
 * @author    Timothy de Paris
 */
class DAuthorisationManager implements DSingleton
{
    use DBaseClass;
    use DSingletonClass;

    /**
     * Initialises the authorisation manager.
     *
     * This will detect any existing login and load the correct user.
     *
     * @return    DAuthorisationManager
     */
    protected function __construct()
    {
        // Use the timezone of the logged in user.
        //		$userTimezone = DUserTimezone::adapt($this->user);
        //		DDate::setTimeZone($userTimezone->getTimeZone());
    }

    /**
     * Returns the user that must take responsibility for actions
     * in the current session.
     *
     * @return    DUser    The user that must take responsibility for actions
     *                    in the current session.
     * @deprecated
     */
    public static function getResponsibleUser()
    {
        return self::getUser();
    }

    /**
     * Returns the number of seconds until the session expires.
     *
     * @return    int        The number of seconds or <code>null</code>
     *                    if the session never expires.
     */
    public static function getSessionExpirySeconds()
    {
        $authorisationManager = self::load();

        return $authorisationManager->result->getSecondsUntilExpiry();
    }

    /**
     * Returns the currently logged in user.
     *
     * @return    DUser    The logged in user, or <code>null</code> if no user
     *                    is logged in.
     */
    public static function getUser()
    {
        return DRootUser::create();
    }

    /**
     * Logs in the provided user.
     *
     * @param    DUser $user The user to log in.
     *
     * @return    void
     */
    public function login(DUser $user)
    {
    }
}
