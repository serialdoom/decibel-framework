<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\authorise\auditing\DAuthenticationRecord;
use app\decibel\authorise\event\DOnPasswordChange;
use app\decibel\regional\DLabel;
use app\decibel\security\DSecurityPolicy;
use app\decibel\utility\DResult;

/**
 * Provides functionality for checking and generating user passwords.
 *
 * @author        Timothy de Paris
 */
class DUserPassword implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Name of the label representing the message to save user before changing
     * the password
     *
     * @var        string
     */
    const LABEL_USER_SAVED_BEFORE_PASSWORD_CHANGED = 'userMustBeSavedBeforePasswordChanged';

    /**
     * Name of the label representing message that current password is missing
     *
     * @var        string
     */
    const LABEL_CURRENT_PASSWORD_MUST_BE_PROVIDED = 'currentPasswordMustBeProvided';

    /**
     * Name of the label representing message that current password is incorrect
     *
     * @var        string
     */
    const LABEL_CURRENT_PASSWORD_IS_INCORRECT = 'currentPasswordInCorrect';

    /**
     * Name of the label representing message that new password must be provided
     *
     * @var        string
     */
    const LABEL_NEW_PASSWORD_MUST_PROVIDED = 'newPasswordMustBeProvided';

    /**
     * Name of the label representing message that provided password does not
     * match
     *
     * @var        string
     */
    const LABEL_PROVIDED_PASSWORD_NOT_MATCH = 'providedPasswordDoNotMatch';

    /**
     * Name of the label representing message that new password must be different
     * from the current passwors
     *
     * @var        string
     */
    const LABEL_NEW_PASSWORD_MUST_DIFFER_CURRENT_PASSWORD = 'newPasswordMustBeDifferentFromCurrentPassword';

    /**
     * Changes the user's password to the provided password.
     *
     * This function will validate that the password meets the security policy
     * for this user before changing it.
     *
     * @param    string $newPassword          The new clear-text password.
     * @param    string $oldPassword          The user's current password. If provided,
     *                                        this must be correct in order to change
     *                                        the password.
     * @param    string $newPasswordVerify    Optional new password confirmation.
     *                                        If provided it will be checked that
     *                                        this matches the new password.
     *
     * @return    DResult
     */
    public function changePassword($newPassword, $oldPassword = null, $newPasswordVerify = null)
    {
        $result = $this->validatePasswordChange($newPassword, $oldPassword, $newPasswordVerify);
        if ($result->isSuccessful()) {
            $onPasswordChange = new DOnPasswordChange();
            $onPasswordChange->setFieldValue('user', $this);
            $this->adaptee->notifyObservers($onPasswordChange);
            // Enrypt the password and update in the database.
            $salt = $this->generateSalt();
            $hash = $this->hashPassword($newPassword, $salt);
            $changeResult = $this->savePasswordToDatabase($hash, $salt);
            if ($changeResult->isSuccessful()) {
                // Log the record of the change password
                DAuthenticationRecord::log(array(
                                               DAuthenticationRecord::FIELD_USERNAME => $this->adaptee->getFieldValue(self::FIELD_USERNAME),
                                               DAuthenticationRecord::FIELD_PROFILE  => $this->adaptee->getFieldValue(self::FIELD_PROFILE),
                                               DAuthenticationRecord::FIELD_ACTION   => DAuthenticationRecord::ACTION_CHANGE_PASSWORD,
                                           ));
            } else {
                $result->setSuccess(false, $changeResult->getMessages());
            }
        }

        return $result;
    }

    /**
     * Checks a provided password to see if it matches the encrypted password
     * stored in the database for this user.
     *
     * @param    string $password     The clear-text password.
     * @param    bool   $log          Whether to log a failed login attempt
     *                                if the password is not valid.
     *
     * @return    bool
     */
    public function checkPassword($password, $log = true)
    {
        // Load salt for this user.
        $salt = $this->adaptee->getFieldValue('salt');
        $currentPassword = $this->adaptee->getFieldValue('password');
        $hash = $this->hashPassword($password, $salt);
        $correct = ($hash === $currentPassword);
        if ($log && !$correct) {
            DAuthenticationRecord::log(array(
                                           DAuthenticationRecord::FIELD_USERNAME => $this->adaptee->getFieldValue(self::FIELD_USERNAME),
                                           DAuthenticationRecord::FIELD_PROFILE  => $this->adaptee->getFieldValue(self::FIELD_PROFILE),
                                           DAuthenticationRecord::FIELD_ACTION   => DAuthenticationRecord::ACTION_FAILED,
                                       ));
        }

        return $correct;
    }

    /**
     * Returns a random password that meets the requirements of this user's
     * security policy.
     *
     * @return    string
     */
    public function generateRandomPassword()
    {
        $profile = $this->adaptee->getFieldValue(DUser::FIELD_PROFILE);
        if (!$profile) {
            $password = $this->generateSalt(10);
        } else {
            $password = $profile->getSecurityPolicy()
                                ->generateRandomPassword();
        }

        return $password;
    }

    /**
     * Generates a random salt that can be used by the password
     * hashing algorithm.
     *
     * @param    int $length The number of bytes to be generated.
     *
     * @return    string
     */
    protected function generateSalt($length = 32)
    {
        return base64_encode(
            mcrypt_create_iv(
                $length,
                MCRYPT_DEV_URANDOM
            )
        );
    }

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
     * Hashes a password with a salt.
     *
     * @param    string $password     The password to hash.
     * @param    string $salt         Salt used to hash the password.
     *                                If <code>null</code>, a new salt will
     *                                be generated and returned in this pointer.
     *
     * @return    string    The hashed password.
     */
    protected function hashPassword($password, &$salt = null)
    {
        // Generate a random salt.
        if ($salt === null) {
            $salt = $this->generateSalt();
        }

        // Hash the password and salt.
        return hash('sha256', $salt . $password);
    }

    /**
     * Updates a password hash and it's salt within the database.
     *
     * @param    string $hash The hashed password.
     * @param    string $salt Salt used to hash the password.
     *
     * @return    DResult
     */
    protected function savePasswordToDatabase($hash, $salt)
    {
        // Update internal data array in case the object
        // is referenced again in this request.
        $this->adaptee->setFieldValue('salt', $salt);
        $this->adaptee->setFieldValue(self::FIELD_PASSWORD, $hash);
        // Temporarily grant the guest user rights to save this user object.
        $adapter = DUserPrivileges::adapt($this->adaptee);
        $adapter->grantPrivilege($this->adaptee->getPrivilegeName(DPrivilege::SUFFIX_EDIT));
        $user = DAuthorisationManager::getUser();

        return $this->adaptee->save($user);
    }

    /**
     * Tests a password against the user's security policy for validity.
     *
     * The following tests will be performed:
     * - The password cannot be blank
     * - The password must match the verification parameter, if provided.
     * - The password cannot be the user's current password.
     * - The password must meet the minimum requirements of the security
     *        policy for this user's profile.
     *
     * @param    string $password         The password to test.
     * @param    string $passwordVerify   Optional password confirmation.
     *                                    If provided it will also be checked that
     *                                    this matches the password.
     *
     * @return    DResult
     */
    public function testPassword($password, $passwordVerify = null)
    {
        $result = new DResult(
            new DLabel(DSecurityPolicy::class, 'passwordTest'),
            new DLabel(DSecurityPolicy::class, 'passed')
        );
        // Check new password if valid.
        if (empty($password)) {
            $message = new DLabel(DUser::class, self::LABEL_NEW_PASSWORD_MUST_PROVIDED);
            $result->setSuccess(false, $message);
            // Check password and passwordVerify are the same.
        } else {
            if ($passwordVerify !== null
                && $passwordVerify !== $password
            ) {
                $message = new DLabel(DUser::class, self::LABEL_PROVIDED_PASSWORD_NOT_MATCH);
                $result->setSuccess(false, $message);
                // Check new and old passwords are not the same.
            } else {
                if ($this->checkPassword($password, false)) {
                    $message = new DLabel(DUser::class, self::LABEL_NEW_PASSWORD_MUST_DIFFER_CURRENT_PASSWORD);
                    $result->setSuccess(false, $message);
                    // Test the password against this user's security policy.
                } else {
                    $profile = $this->adaptee->getFieldValue(self::FIELD_PROFILE);
                    if ($profile) {
                        $policy = $profile->getSecurityPolicy();
                        $result->merge($policy->testPassword($password));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Validates the provided password change parameters.
     *
     * @param    string $newPassword          The new clear-text password.
     * @param    string $oldPassword          The user's current password. If provided,
     *                                        this must be correct in order to change
     *                                        the password.
     * @param    string $newPasswordVerify    Optional new password confirmation.
     *                                        If provided it will be checked that
     *                                        this matches the new password.
     *
     * @return    DResult
     */
    protected function validatePasswordChange($newPassword,
                                              $oldPassword = null, $newPasswordVerify = null)
    {
        $result = new DResult('Password', 'changed');
        if ($this->adaptee->getId() === 0) {
            $message = new DLabel(DUser::class, self::LABEL_USER_SAVED_BEFORE_PASSWORD_CHANGED);
            $result->setSuccess(false, $message);
            // Check the supplied current password was correct.
        } else {
            if ($oldPassword === '') {
                $message = new DLabel(DUser::class, self::LABEL_CURRENT_PASSWORD_MUST_BE_PROVIDED);
                $result->setSuccess(false, $message, 'app-decibel-authorise-currentPassword');
            } else {
                if ($oldPassword !== null
                    && !$this->checkPassword($oldPassword)
                ) {
                    $message = new DLabel(DUser::class, self::LABEL_CURRENT_PASSWORD_IS_INCORRECT);
                    $result->setSuccess(false, $message, 'app-decibel-authorise-currentPassword');
                    // Test the new password.
                } else {
                    $result->merge(
                        $this->testPassword($newPassword, $newPasswordVerify)
                    );
                }
            }
        }

        return $result;
    }
}
