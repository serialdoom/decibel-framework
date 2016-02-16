<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\application\DApp;
use app\decibel\authorise\auditing\DAuthenticationRecord;
use app\decibel\authorise\debug\DUnprivilegedException;
use app\decibel\authorise\DGroup;
use app\decibel\authorise\DInvalidPrivilegeException;
use app\decibel\authorise\DProfile;
use app\decibel\authorise\event\DOnPasswordChange;
use app\decibel\authorise\event\DOnUserFirstLogin;
use app\decibel\authorise\event\DOnUserLogin;
use app\decibel\authorise\event\DOnUserLogout;
use app\decibel\configuration\DApplicationMode;
use app\decibel\model\DModel;
use app\decibel\security\DSecurityPolicy;
use app\decibel\utility\DResult;

/**
 * Base class for user accounts allowing authorisation within an application.
 *
 * The following decorators provide additional functionality for users:
 * - {@link DUserCapabilityCode}: for generating a user's capability code.
 * - {@link DUserGroups}: for managing a user's group memberships.
 * - {@link DUserPassword}: for changing, testing and validating passwords.
 * - {@link DUserPrivileges}: for checking a user's privileges.
 * - {@link DUserTimezone}: for working with user timezones.
 *
 * @author        Timothy de Paris
 */
class DUser extends DModel
{
    /**
     * 'E-mail' field name.
     *
     * @var        string
     */
    const FIELD_EMAIL = 'email';

    /**
     * 'First Name' field name.
     *
     * @var        string
     */
    const FIELD_FIRST_NAME = 'firstName';

    /**
     * 'Groups' field name.
     *
     * @var        string
     */
    const FIELD_GROUPS = 'groups';

    /**
     * 'Last Name' field name.
     *
     * @var        string
     */
    const FIELD_LAST_NAME = 'lastName';

    /**
     * 'Lockout Status' field name.
     *
     * @var        string
     */
    const FIELD_LOCKOUT_STATUS = 'lockoutStatus';

    /**
     * 'Password' field name.
     *
     * @var        string
     */
    const FIELD_PASSWORD = 'password';

    /**
     * 'Profile' field name.
     *
     * @var        string
     */
    const FIELD_PROFILE = 'profile';

    /**
     * 'Timezone' field name.
     *
     * @var        string
     */
    const FIELD_TIMEZONE = 'timezone';

    /**
     * 'Username' field name.
     *
     * @var        string
     */
    const FIELD_USERNAME = 'username';

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::authorise::event::DOnUserFirstLogin DOnUserFirstLogin}
     * event.
     *
     * @var        string
     */
    const ON_FIRST_LOGIN = DOnUserFirstLogin::class;
    /**
     * Reference to the qualified name of the
     * {@link app::decibel::authorise::event::DOnPasswordChange DOnPasswordChange}
     * event.
     *
     * @var        string
     */
    const ON_PASSWORD_CHANGE = DOnPasswordChange::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::authorise::event::DOnUserLogin DOnUserLogin}
     * event.
     *
     * @var        string
     */
    const ON_LOGIN = DOnUserLogin::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::authorise::event::DOnUserLogout DOnUserLogout}
     * event.
     *
     * @var        string
     */
    const ON_LOGOUT = DOnUserLogout::class;

    /**
     * Automatic account lockout status.
     *
     * This status denotes that an account has been automatically locked out
     * by Decibel, due to a breach of the current security policy.
     *
     * @var        int
     */
    const LOCKOUT_STATUS_AUTOMATIC = 1;
    /**
     * Manual account lockout status.
     *
     * This status denotes that an account has been manually locked out
     * by an Administrator.
     *
     * @var        int
     */
    const LOCKOUT_STATUS_MANUAL = 2;

    /**
     * Available lockout status options.
     *
     * @var        array
     */
    public static $lockoutOptions = array(
        self::LOCKOUT_STATUS_AUTOMATIC => 'Automatic Lockout',
        self::LOCKOUT_STATUS_MANUAL    => 'Manual Lockout',
    );

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        $events = parent::getEvents();
        $events[] = self::ON_FIRST_LOGIN;
        $events[] = self::ON_LOGIN;
        $events[] = self::ON_LOGOUT;
        $events[] = self::ON_PASSWORD_CHANGE;

        return $events;
    }

    /**
     * Returns an array containing the IDs of each model instance that this
     * model instance holds a link to.
     *
     * @return    array
     */
    protected function getIdsToFree()
    {
        $toFree = parent::getIdsToFree();
        $userGroups = DUserGroups::adapt($this);
        foreach ($userGroups->getGroupPointers() as $groupId => $group) {
            /* @var $group DGroup */
            if (!in_array($groupId, $toFree)) {
                $toFree[] = $groupId;
                $toFree = array_merge($toFree, $group->getIdsToFree());
            }
        }

        return $toFree;
    }

    /**
     * Determines if this user account has been locked.
     *
     * Implementing User classes may allow an account to be locked.
     * If so, this function should return <code>true</code>
     * if an account has been locked.
     *
     * If no lockout functionality is required, this function may be overridden
     * to always return <code>false</code>.
     *
     * @return    bool
     */
    public function accountLocked()
    {
        return ($this->getFieldValue(self::FIELD_LOCKOUT_STATUS) !== null);
    }

    /**
     * Returns the language in which this user prefers content to be delivered.
     *
     * @return    string    The language code.
     */
    public function getLanguage()
    {
        return $this->getFieldValue('language');
    }

    /**
     * Returns the formatted name of this user.
     *
     * @note
     * If this user has no name (e.g. a newly created user object),
     * this function will return 'Unknown User'.
     *
     * @return    string
     */
    public function getName()
    {
        $firstName = $this->getFieldValue(self::FIELD_FIRST_NAME);
        $lastName = $this->getFieldValue(self::FIELD_LAST_NAME);
        // User does not exist.
        if (!$firstName && !$lastName) {
            $name = 'Unknown&nbsp;User';
        } else {
            $name = "{$firstName} {$lastName}";
        }

        return $name;
    }

    /**
     * Determines the number of times the user has logged in.
     *
     * @return    int
     */
    public function getLoginCount()
    {
        return $this->getFieldValue('loginCount');
    }

    /**
     * Returns the profile to which this user belongs.
     *
     * @return    DProfile
     */
    public function getProfile()
    {
        return $this->getFieldValue(self::FIELD_PROFILE);
    }

    /**
     * Determines the number of minutes this user's login can remain
     * active for.
     *
     * @return    int        The number of minutes this user's login can remain
     *                    active for, or <code>null</code> if no expiry should
     *                    be applied.
     */
    public function getSessionExpiryTime()
    {
        // Get the security policy for this user.
        $profile = $this->getFieldValue(self::FIELD_PROFILE);
        $policy = $profile->getSecurityPolicy();
        $sessionTimeout = $policy->getFieldValue(DSecurityPolicy::FIELD_SESSION_TIMEOUT);
        if ($sessionTimeout === 0) {
            $sessionTimeout = null;
        }

        return $sessionTimeout;
    }

    /**
     * Determines the maximum number of sessions a user can have
     * (i.e. the number of times the user can log in through different
     * browser instances).
     *
     * @return    int        The maximum number of sessions for the user, or
     *                    <code>null</code> if no limit should be applied.
     */
    public function getSessionLimit()
    {
        // Get the security policy for this user.
        $profile = $this->getProfile();
        $policy = $profile->getSecurityPolicy();

        return $policy->getFieldValue(DSecurityPolicy::FIELD_SESSION_LIMIT);
    }

    /**
     * Returns a description of the current status of this user.
     *
     * @return    string
     */
    public function getStatus()
    {
        if ($this->getFieldValue(self::FIELD_LOCKOUT_STATUS) !== null) {
            $status = 'Locked';
        } else {
            $status = 'Active';
        }

        return $status;
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    protected function getStringValue()
    {
        $firstName = $this->getFieldValue(self::FIELD_FIRST_NAME);
        $lastName = $this->getFieldValue(self::FIELD_LAST_NAME);
        $email = $this->getFieldValue(self::FIELD_EMAIL);

        return "{$firstName} {$lastName} ({$email})";
    }

    ///@cond INTERNAL
    /**
     * Determines if this user has a specified privilege.
     *
     * Privileges are inherited from the Groups a user is assigned to.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    bool
     * @throws    DInvalidPrivilegeException    If the specified privilege does not exist.
     * @deprecated    In favour of {@link app::decibel::authorise::DUserPrivileges::hasPrivilege()
     *                DUserPrivileges::hasPrivilege()}
     */
    public function hasPrivilege($privilege)
    {
        // in the case the application mode is set to test
        // we don't need permission; WE DO IN ALL OTHER CASES
        if (DApplicationMode::isTestMode()) {
            return true;
        }
               $adapter = DUserPrivileges::adapt($this);
        return $adapter->hasPrivilege($privilege);
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Determines if this user is a member of the specified group.
     *
     * @param    DGroup $group The group to test.
     *
     * @return    bool
     * @deprecated    In favour of {@link app::decibel::authorise::DUserGroups::isGroupMember()
     *                DUserGroups::isGroupMember()}
     */
    public function isGroupMember(DGroup $group)
    {
        $adapter = DUserGroups::adapt($this);

        return $adapter->isGroupMember($group);
    }
    ///@endcond
    /**
     * Performs any functionality required to log the user in.
     *
     * This function by itself does not perform a log in, however is called
     * by {@link DAuthorisationManager}.
     *
     * This function will trigger the {@link DOnUserLogin} event
     * and the {@link DOnUserFirstLogin} event if appropriate.
     *
     * @return    void
     */
    public function login()
    {
        // Keep audit trail.
        DAuthenticationRecord::log(array(
                                       DAuthenticationRecord::FIELD_USERNAME => $this->getFieldValue(self::FIELD_USERNAME),
                                       DAuthenticationRecord::FIELD_PROFILE  => $this->getFieldValue(self::FIELD_PROFILE),
                                       DAuthenticationRecord::FIELD_ACTION   => DAuthenticationRecord::ACTION_LOGIN,
                                   ));
        // If this is the first time the user logged in,
        // trigger the first login event.
        if ($this->getLoginCount() == 1) {
            $event = new DOnUserFirstLogin();
            $this->notifyObservers($event);
        }
        $event = new DOnUserLogin();
        $this->notifyObservers($event);
    }

    /**
     * Performs any functionality required to log the user out.
     *
     * This function by itself does not perform a log out, however is called
     * by {@link DAuthorisationManager}.
     *
     * This function will trigger the {@link DOnUserLogout} event.
     *
     * @param    int $reason      The reason the user was logged out. This must
     *                            be one of the logout actions defined by
     *                            {@link DAuthenticationRecord::ACTION_LOGOUT}.
     *
     * @return    void
     */
    public function logout($reason = DAuthenticationRecord::ACTION_LOGOUT)
    {
        // Keep audit trail.
        DAuthenticationRecord::log(array(
                                       DAuthenticationRecord::FIELD_USERNAME => $this->getFieldValue(self::FIELD_USERNAME),
                                       DAuthenticationRecord::FIELD_PROFILE  => $this->getFieldValue(self::FIELD_PROFILE),
                                       DAuthenticationRecord::FIELD_ACTION   => $reason,
                                   ));
        $event = new DOnUserLogout();
        $this->notifyObservers($event);
    }

    /**
     * Presets the profile for this user if there is only one available profile.
     *
     * Called on the {@link app::decibel::model::DModel::ON_LOAD} event.
     *
     * @return    void
     */
    protected function setInitialProfile()
    {
        // Don't do anything if a Profile is already set.
        $profile = $this->getFieldValue(self::FIELD_PROFILE);
        if ($profile === null) {
            // Look for profiles available to this user type.
            $profiles = DProfile::search()
                                ->filterByField(DProfile::FIELD_USER_OBJECT, get_class($this))
                                ->getIds();
            // If only one profile is available, select it.
            if (count($profiles) === 1) {
                $this->setFieldValue(self::FIELD_PROFILE, DProfile::create($profiles[0]));
            }
        }
    }

    /**
     * Determines if a user is authorised to save this object.
     *
     * This overrides the {@link app::decibel::model::DBaseModel::userCanSave() DBaseModel::userCanSave()}
     * method to ensure that a user always has the right to save their own model.
     *
     * @param    DUser $user The user to test.
     *
     * @return    DResult
     * @throws    DUnprivilegedException    If the user does not have the required privilege.
     */
    public function userCanSave(DUser $user)
    {
        if ($user->id === $this->id) {
            $result = new DResult($this->displayName, 'saved');
        } else {
            $result = parent::userCanSave($user);
        }

        return $result;
    }
}
