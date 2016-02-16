<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

/**
 * A representation of an authorised user session with the application.
 *
 * @author        Timothy de Paris
 */
interface DUserSession
{
    /**
     * Registers a new session for the provided user.
     *
     * @note
     * It is assumed that the provided user has been successfully authenticated
     * before this method is called.
     *
     * @param    DUser $user
     *
     * @return    static
     */
    public static function create(DUser $user);

    /**
     * Loads the session with the specified token.
     *
     * @param    string $token
     *
     * @return    static
     */
    public static function getWithToken($token);

    /**
     * Returns the expiry time of this session.
     *
     * @return    int
     */
    public function getExpiry();

    /**
     * Returns the token associated with this session.
     *
     * @return    string
     */
    public function getToken();

    /**
     * Returns the user associated with this session.
     *
     * @return    DUser
     */
    public function getUser();

    /**
     * Invalidates the token, ending the session for the associated user.
     *
     * @return    void
     */
    public function invalidate();

    /**
     * Updates the expiry time on the session.
     *
     * This is usually called whenever a user accesses the application, in order to extend
     * the life of the session.
     *
     * @return    void
     */
    public function update();
}
