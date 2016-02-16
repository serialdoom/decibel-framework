<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise\auditing;

use app\decibel\auditing\DAuditRecord;
use app\decibel\authorise\DProfile;
use app\decibel\authorise\DUser;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\http\request\DRequest;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\field\DTextField;
use app\decibel\regional\DLabel;
use app\decibel\security\DSecurityPolicy;
use app\decibel\utility\DResult;

/**
 * Audit record for authentication events.
 *
 * @author    Timothy de Paris
 */
class DAuthenticationRecord extends DAuditRecord
{
    /**
     * Autentication record action for a successful login.
     *
     * @var        int
     */
    const ACTION_LOGIN = 1;

    /**
     * Autentication record action for a login session being ended by the user.
     *
     * @var        int
     */
    const ACTION_LOGOUT = 2;

    /**
     * Autentication record action for a failed login.
     *
     * @var        int
     */
    const ACTION_FAILED = 3;

    /**
     * Autentication record action for a login session expiring.
     *
     * @var        int
     */
    const ACTION_EXPIRED = 4;

    /**
     * Autentication record action for an account being automatically locked.
     *
     * @var        int
     */
    const ACTION_AUTOMATIC_LOCK = 5;

    /**
     * Autentication record action for an account being manually locked.
     *
     * @var        int
     */
    const ACTION_MANUAL_LOCK = 6;

    /**
     * Autentication record action for an account being unlocked.
     *
     * @var        int
     */
    const ACTION_UNLOCK = 7;

    /**
     * Autentication record action for a changed passsword.
     *
     * @var        int
     */
    const ACTION_CHANGE_PASSWORD = 9;

    /**
     * Autentication record action for an account that
     * has reached it's session limit.
     *
     * @var        int
     */
    const ACTION_SESSION_LIMIT = 10;

    /**
     * Autentication record action for a login attempt with an incorrect
     * CSRF token.
     *
     * @var        int
     */
    const ACTION_INVALID_TOKEN = 11;

    /**
     * 'Action' field name.
     *
     * @var        string
     */
    const FIELD_ACTION = 'action';

    /**
     * 'IP Address' field name.
     *
     * @var        string
     */
    const FIELD_IP_ADDRESS = 'ipAddress';

    /**
     * 'Notes' field name.
     *
     * @var        string
     */
    const FIELD_NOTES = 'notes';

    /**
     * 'Profile' field name.
     *
     * @var        string
     */
    const FIELD_PROFILE = 'profile';

    /**
     * 'Username' field name.
     *
     * @var        string
     */
    const FIELD_USERNAME = 'username';

    /**
     * Authentication record actions.
     *
     * @var        array
     */
    private static $actions;

    /**
     * Defines fields and indexes required by this audit record.
     *
     * @return    void
     */
    protected function define()
    {
        $labelNone = new DLabel('app\\decibel', 'none');
        $ipAddress = new DTextField(self::FIELD_IP_ADDRESS, new DLabel(self::class, self::FIELD_IP_ADDRESS));
        $ipAddress->setMaxLength(16);
        $this->addField($ipAddress);
        $username = new DTextField(self::FIELD_USERNAME, new DLabel(self::class, self::FIELD_USERNAME));
        $username->setMaxLength(255);
        $this->addField($username);
        $action = new DEnumField(self::FIELD_ACTION, new DLabel(self::class, self::FIELD_ACTION));
        $action->setValues(self::getActions());
        $this->addField($action);
        $profile = new DLinkedObjectField(self::FIELD_PROFILE, new DLabel(self::class, self::FIELD_PROFILE));
        $profile->setNullOption('Unknown');
        $profile->setLinkTo(DProfile::class);
        $this->addField($profile);
        $notes = new DTextField(self::FIELD_NOTES, new DLabel(self::class, self::FIELD_NOTES));
        $notes->setNullOption($labelNone);
        $notes->setMaxLength(255);
        $this->addField($notes);
    }

    /**
     * Returns available actions for an authentication record.
     *
     * @return    array
     */
    public static function getActions()
    {
        if (!self::$actions) {
            self::$actions = array(
                self::ACTION_LOGIN           => new DLabel(self::class, 'actionLogin'),
                self::ACTION_LOGOUT          => new DLabel(self::class, 'actionLogout'),
                self::ACTION_FAILED          => new DLabel(self::class, 'actionFailed'),
                self::ACTION_EXPIRED         => new DLabel(self::class, 'actionExpired'),
                self::ACTION_SESSION_LIMIT   => new DLabel(self::class, 'actionSessionLimit'),
                self::ACTION_INVALID_TOKEN   => new DLabel(self::class, 'actionInvalidToken'),
                self::ACTION_AUTOMATIC_LOCK  => new DLabel(self::class, 'actionAutomaticLock'),
                self::ACTION_MANUAL_LOCK     => new DLabel(self::class, 'actionManualLock'),
                self::ACTION_UNLOCK          => new DLabel(self::class, 'actionUnlock'),
                self::ACTION_CHANGE_PASSWORD => new DLabel(self::class, 'actionChangePassword'),
            );
        }

        return self::$actions;
    }

    /**
     * Log data in the audit log.
     *
     * @param    array $data Key/value data pairs.
     *
     * @return    DResult
     * @throws    DInvalidPropertyException    If no field exists with this name.
     * @throws    DInvalidFieldValueException    If an invalid value for the field
     *                                        is provided.
     */
    public static function log(array $data)
    {
        // Add IP address to the data array.
        $request = DRequest::load();
        $data[ self::FIELD_IP_ADDRESS ] = $request->getIpAddress();

        return parent::log($data);
    }

    /**
     * Tests whether a possible brute force attack could be occurring
     * from the current client.
     *
     * @param    DUser $user      The user to test for brute force attempts.
     *                            If not provided, the client IP address will
     *                            be tested, irrespective of username entered.
     *
     * @return    bool
     */
    public static function testBruteForce(DUser $user = null)
    {
        $search = self::search()
                      ->filterByField(self::FIELD_ACTION, self::ACTION_FAILED);
        if ($user === null) {
            $period = 1800;
            $maxAttempts = 30;
            $request = DRequest::load();
            $search->filterByField(self::FIELD_IP_ADDRESS, $request->getIpAddress());
        } else {
            $policy = $user->getProfile()->getSecurityPolicy();
            $period = $policy->getFieldValue(DSecurityPolicy::FIELD_LOCKOUT_LENGTH) * 60;
            $maxAttempts = $policy->getFieldValue(DSecurityPolicy::FIELD_FAILED_LOGIN_LOCKOUT);
            $search->filterByField(self::FIELD_USERNAME, $user->getFieldValue(DUser::FIELD_USERNAME));
        }
        $attempts = $search
            ->filterByField(self::FIELD_CREATED, time() - $period, DFieldSearch::OPERATOR_GREATER_THAN)
            ->getCount();

        return ($attempts > $maxAttempts);
    }
}
