<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\model\DLightModel;

/**
 * Represents an authenticated user session.
 *
 * @author    Timothy de Paris
 */
class DSessionToken extends DLightModel
{
    /**
     * 'Session Expiry' field name.
     *
     * @var        string
     */
    const FIELD_EXPIRY = 'expiry';
    /**
     * 'Last Updated' field name.
     *
     * @var        string
     */
    const FIELD_LAST_UPDATED = 'lastUpdated';
    /**
     * 'Token' field name.
     *
     * @var        string
     */
    const FIELD_TOKEN = 'token';
    /**
     * 'User' field name.
     *
     * @var        string
     */
    const FIELD_USER = 'user';

    /**
     * Returns the time at which this session will expire.
     *
     * @return    int
     */
    public function getExpiryTime()
    {
        return $this->getFieldValue(self::FIELD_EXPIRY);
    }

    /**
     * Returns the time at which this session information was last updated.
     *
     * @return    int
     */
    public function getLastUpdated()
    {
        return $this->getFieldValue(self::FIELD_LAST_UPDATED);
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    public function getStringValue()
    {
        $user = $this->getFieldValue(self::FIELD_USER);
        $token = $this->getFieldValue(self::FIELD_TOKEN);

        return "{$user} ({$token})";
    }

    /**
     * Returns the token identifying this session.
     *
     * @return    string
     */
    public function getToken()
    {
        return $this->getFieldValue(self::FIELD_TOKEN);
    }

    /**
     * Returns the user associated with this session.
     *
     * @return    DUser
     */
    public function getUser()
    {
        return $this->getFieldValue(self::FIELD_USER);
    }

    /**
     * Updates the last updated time for this instance.
     *
     * @return    void
     */
    protected function setLastUpdated()
    {
        $this->setFieldValue(self::FIELD_LAST_UPDATED, time);
    }
}
