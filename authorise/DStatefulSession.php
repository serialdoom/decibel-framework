<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\utility\DSession;

/**
 * Provides functionality to manage stateful authenticated sessions,
 * using the PHP Session cookie.
 *
 * @author        Timothy de Paris
 */
class DStatefulSession extends DStatelessSession
{
    /**
     * Returns the currently authenticated session, if any.
     *
     * @return    static    The session, or <code>null</code> if no session has been created.
     */
    public static function getCurrentSession()
    {
        $session = DSession::load();

        return static::getWithToken($session->getId());
    }

    /**
     * Generates a token for an authenticated session.
     *
     * @return    string
     */
    protected static function generateToken()
    {
        $session = DSession::load();
        // Re-generate the session ID.
        $session->regenerateId();

        return $session->getId();
    }

    /**
     * Invalidates the token, ending the session for the associated user.
     *
     * @return    void
     */
    public function invalidate()
    {
        parent::invalidate();
        $session = DSession::load();
        $session->end();
    }
}
