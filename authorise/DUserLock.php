<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\authorise\auditing\DAuthenticationRecord;
use app\decibel\utility\DResult;

/**
 * Provides functionality for locking and unlocking user accounts.
 *
 * @author        Timothy de Paris
 */
class DUserLock implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DUser::class;
    }

    /**
     * Locks this user's account.
     *
     * @param    string $reason Optional reason for locking the account.
     *
     * @return    DResult
     */
    public function lockAccount($reason = null)
    {
        $user = $this->getAdaptee();
        $result = new DResult('Account', 'locked');
        // Check that this account is unlocked.
        if ($user->getFieldValue(self::FIELD_LOCKOUT_STATUS) !== null) {
            $result->setSuccess(false, 'Account is already locked.');
        } else {
            // Set the lockout parameters.
            $user->setFieldValue(self::FIELD_LOCKOUT_STATUS, self::LOCKOUT_STATUS_MANUAL);
            $user->setFieldValue('lockoutExpiry', null);
            $user->setFieldValue('lockoutReason', $reason);
            // Attempt to save the user.
            $currentUser = DAuthorisationManager::getUser();
            $result->merge($user->save($currentUser));
            // Keep audit trail.
            if ($result->isSuccessful()) {
                DAuthenticationRecord::log(array(
                                               DAuthenticationRecord::FIELD_USERNAME => $user->getFieldValue(self::FIELD_USERNAME),
                                               DAuthenticationRecord::FIELD_PROFILE  => $user->getFieldValue(self::FIELD_PROFILE),
                                               DAuthenticationRecord::FIELD_ACTION   => DAuthenticationRecord::ACTION_MANUAL_LOCK,
                                               DAuthenticationRecord::FIELD_NOTES    => $reason,
                                           ));
            }
        }

        return $result;
    }

    /**
     * Unlocks this user's account.
     *
     * @return    DResult
     */
    public function unlockAccount()
    {
        $user = $this->getAdaptee();
        $result = new DResult('Account', 'unlocked');
        // Check that this account is locked.
        // Old databases may use 0 instead of null
        // so don't perform a strict check.
        if (!$user->getFieldValue(self::FIELD_LOCKOUT_STATUS)) {
            $result->setSuccess(false, 'Account is not locked.');
        } else {
            // Set the lockout parameters.
            $user->setFieldValue(self::FIELD_LOCKOUT_STATUS, null);
            $user->setFieldValue('lockoutExpiry', null);
            $user->setFieldValue('lockoutReason', null);
            // Attempt to save the user.
            $currentUser = DAuthorisationManager::getUser();
            $result->merge($user->save($currentUser));
            // Keep audit trail.
            if ($result->isSuccessful()) {
                DAuthenticationRecord::log(array(
                                               DAuthenticationRecord::FIELD_USERNAME => $user->getFieldValue(self::FIELD_USERNAME),
                                               DAuthenticationRecord::FIELD_PROFILE  => $user->getFieldValue(self::FIELD_PROFILE),
                                               DAuthenticationRecord::FIELD_ACTION   => DAuthenticationRecord::ACTION_UNLOCK,
                                           ));
            }
        }

        return $result;
    }
}
