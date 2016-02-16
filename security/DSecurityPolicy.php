<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\security;

use app\decibel\authorise\DUser;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DEnumField;
use app\decibel\model\field\DIntegerField;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;
use app\decibel\utility\DUtilityData;

/**
 * The {@link DSecurityPolicy} class stores information describing
 * the security policy to be applied a particular set of users by Decibel.
 *
 * @author        Timothy de Paris
 */
abstract class DSecurityPolicy extends DUtilityData
{
    /**
     * One-factor authentication.
     *
     * @var        int
     */
    const AUTH_FACTORS_ONE = 1;

    /**
     * Two-factor authentication.
     *
     * @var        int
     */
    const AUTH_FACTORS_TWO = 2;

    /**
     * 'Auth Factor Requirement' field name.
     *
     * @var        string
     */
    const FIELD_AUTH_FACTOR_REQUIREMENT = 'authFactorRequirement';

    /**
     * 'Failed Login Lockout' field name.
     *
     * @var        string
     */
    const FIELD_FAILED_LOGIN_LOCKOUT = 'failedLoginLockout';

    /**
     * 'Inactive Lockout' field name.
     *
     * @var        string
     */
    const FIELD_INACTIVE_LOCKOUT = 'inactiveLockout';

    /**
     * 'Lockout Length' field name.
     *
     * @var        string
     */
    const FIELD_LOCKOUT_LENGTH = 'lockoutLength';

    /**
     * 'Minimum Password Length' field name.
     *
     * @var        string
     */
    const FIELD_MINIMUM_PASSWORD_LENGTH = 'minimumPasswordLength';

    /**
     * 'Override Condition' field name.
     *
     * @var        string
     */
    const FIELD_OVERRIDE_CONDITION = 'overrideCondition';

    /**
     * 'Password Life' field name.
     *
     * @var        string
     */
    const FIELD_PASSWORD_LIFE = 'passwordLife';

    /**
     * 'Password Strength' field name.
     *
     * @var        string
     */
    const FIELD_PASSWORD_STRENGTH = 'passwordStrength';

    /**
     * 'Remembered Passwords' field name.
     *
     * @var        string
     */
    const FIELD_REMEMBERED_PASSWORDS = 'rememberedPasswords';

    /**
     * 'Session Limit' field name.
     *
     * @var        string
     */
    const FIELD_SESSION_LIMIT = 'sessionLimit';

    /**
     * 'Session Timeout' field name.
     *
     * @var        string
     */
    const FIELD_SESSION_TIMEOUT = 'sessionTimeout';

    /**
     * Weaker override permitted.
     *
     * @var        int
     */
    const OVERRIDE_WEAKER = 1;

    /**
     * No override permitted.
     *
     * @var        int
     */
    const OVERRIDE_NONE = 2;

    /**
     * Stronger override permitted.
     *
     * @var        int
     */
    const OVERRIDE_STRONGER = 3;

    /**
     * Low password strength.
     *
     * @var        int
     */
    const PASSWORD_STRENGTH_LOW = 1;

    /**
     * Medium password strength.
     *
     * @var        int
     */
    const PASSWORD_STRENGTH_MEDIUM = 2;

    /**
     * High password strength.
     *
     * @var        int
     */
    const PASSWORD_STRENGTH_HIGH = 3;

    /**
     * Available characters for use when generating a random password.
     *
     * @var        string
     */
    const RANDOM_PASSWORD_CHARS = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!$%^&*(){}[]?@#=-<>";

    /**
     * Creates the security policy and sets the default options.
     *
     * @return    static
     */
    public function __construct()
    {
        parent::__construct();
        $this->initialise();
    }

    /**
     * Ensures correct options are applied to the security policy
     * when unserialized.
     *
     * @return    void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->initialise();
    }

    /**
     * Defines fields available for this utility data object
     *
     * @return    void
     */
    protected function define()
    {
        $neverLabel = new DLabel('app\\decibel', 'never');
        $unlimitedLabel = new DLabel('app\\decibel', 'unlimited');
        $overrideCondition = new DEnumField(self::FIELD_OVERRIDE_CONDITION,
                                            new DLabel(self::class, self::FIELD_OVERRIDE_CONDITION));
        $overrideCondition->setDescription(new DLabel(self::class, 'overrideConditionDescription'));
        $overrideCondition->setValues(DSecurityPolicy::getOverrideConditionOptions());
        $overrideCondition->setDefault(DSecurityPolicy::OVERRIDE_STRONGER);
        $this->addField($overrideCondition);
        $sessionTimeout = new DIntegerField(self::FIELD_SESSION_TIMEOUT,
                                            new DLabel(self::class, self::FIELD_SESSION_TIMEOUT));
        $sessionTimeout->setDescription(new DLabel(self::class, 'sessionTimeoutDescription'));
        $sessionTimeout->setNullOption($neverLabel);
        $sessionTimeout->setStart(15);
        $sessionTimeout->setEnd(240);
        $sessionTimeout->setStep(15);
        $sessionTimeout->setDefault(60);
        $this->addField($sessionTimeout);
        $sessionLimit = new DIntegerField(self::FIELD_SESSION_LIMIT,
                                          new DLabel(self::class, self::FIELD_SESSION_LIMIT));
        $sessionLimit->setDescription(new DLabel(self::class, 'maximumSessionsDescription'));
        $sessionLimit->setNullOption($unlimitedLabel);
        $sessionLimit->setStart(1);
        $sessionLimit->setEnd(10);
        $sessionLimit->setDefault(null);
        $this->addField($sessionLimit);
        $inactiveLockout = new DIntegerField(self::FIELD_INACTIVE_LOCKOUT,
                                             new DLabel(self::class, self::FIELD_INACTIVE_LOCKOUT));
        $inactiveLockout->setDescription(new DLabel(self::class, 'inactiveLockoutDescription'));
        $inactiveLockout->setNullOption($neverLabel);
        $inactiveLockout->setStart(30);
        $inactiveLockout->setEnd(120);
        $inactiveLockout->setStep(30);
        $inactiveLockout->setDefault(null);
        $this->addField($inactiveLockout);
        $failedLoginLockout = new DIntegerField(self::FIELD_FAILED_LOGIN_LOCKOUT,
                                                new DLabel(self::class, self::FIELD_FAILED_LOGIN_LOCKOUT));
        $failedLoginLockout->setDescription(new DLabel(self::class, 'failedLoginLockoutDescription'));
        $failedLoginLockout->setNullOption($unlimitedLabel);
        $failedLoginLockout->setStart(1);
        $failedLoginLockout->setEnd(10);
        $failedLoginLockout->setDefault(10);
        $this->addField($failedLoginLockout);
        $lockoutLength = new DIntegerField(self::FIELD_LOCKOUT_LENGTH,
                                           new DLabel(self::class, self::FIELD_LOCKOUT_LENGTH));
        $lockoutLength->setDescription(new DLabel(self::class, 'lockoutLengthDescription'));
        $lockoutLength->setNullOption('Manual Activation');
        $lockoutLength->setStart(10);
        $lockoutLength->setEnd(90);
        $lockoutLength->setStep(10);
        $lockoutLength->setDefault(10);
        $this->addField($lockoutLength);
        $authFactorRequirement = new DEnumField(self::FIELD_AUTH_FACTOR_REQUIREMENT,
                                                new DLabel(self::class, self::FIELD_AUTH_FACTOR_REQUIREMENT));
        $authFactorRequirement->setDescription(new DLabel(self::class, 'authFactorRequirementDescription'));
        $authFactorRequirement->setValues(DSecurityPolicy::getAuthFactorOptions());
        $authFactorRequirement->setDefault(DSecurityPolicy::AUTH_FACTORS_ONE);
        $this->addField($authFactorRequirement);
        $passwordStrength = new DEnumField(self::FIELD_PASSWORD_STRENGTH,
                                           new DLabel(self::class, self::FIELD_PASSWORD_STRENGTH));
        $passwordStrength->setDescription(new DLabel(self::class, 'passwordStrengthDescription'));
        $passwordStrength->setValues(DSecurityPolicy::getPasswordStrengthOptions());
        $passwordStrength->setDefault(DSecurityPolicy::PASSWORD_STRENGTH_MEDIUM);
        $this->addField($passwordStrength);
        $passwordLife = new DIntegerField(self::FIELD_PASSWORD_LIFE,
                                          new DLabel(self::class, self::FIELD_PASSWORD_LIFE));
        $passwordLife->setDescription(new DLabel(self::class, 'passwordLifeDescription'));
        $passwordLife->setNullOption($unlimitedLabel);
        $passwordLife->setStart(30);
        $passwordLife->setEnd(120);
        $passwordLife->setStep(30);
        $passwordLife->setDefault(null);
        $this->addField($passwordLife);
        $minimumPasswordLength = new DIntegerField(self::FIELD_MINIMUM_PASSWORD_LENGTH, new DLabel(self::class,
                                                                                                   self::FIELD_MINIMUM_PASSWORD_LENGTH));
        $minimumPasswordLength->setDescription(new DLabel(self::class, 'minimumPasswordLengthDescription'));
        $minimumPasswordLength->setStart(5);
        $minimumPasswordLength->setEnd(14);
        $minimumPasswordLength->setDefault(6);
        $this->addField($minimumPasswordLength);
        $rememberedPasswords = new DIntegerField(self::FIELD_REMEMBERED_PASSWORDS,
                                                 new DLabel(self::class, self::FIELD_REMEMBERED_PASSWORDS));
        $rememberedPasswords->setDescription(new DLabel(self::class, 'rememberedPasswordsDescription'));
        $rememberedPasswords->setNullOption('None');
        $rememberedPasswords->setStart(1);
        $rememberedPasswords->setEnd(10);
        $rememberedPasswords->setDefault(null);
        $this->addField($rememberedPasswords);
    }

    /**
     * Generates a random password that meets the requirement of this policy.
     *
     * @return    string    The generated password.
     */
    public function generateRandomPassword()
    {
        // Set up password generator.
        srand((double)microtime() * getrandmax());
        // Generate random passwords until one is created that meets the
        // security policy requirements.
        do {
            // Generate password.
            $password = '';
            for ($i = 0; $i <= $this->getFieldValue(self::FIELD_MINIMUM_PASSWORD_LENGTH); ++$i) {
                $char = rand() % strlen(static::RANDOM_PASSWORD_CHARS);
                $password .= substr(static::RANDOM_PASSWORD_CHARS, $char, 1);
            }
            // Test password.
            $strongEnough = $this->testPassword($password);
        } while (!$strongEnough->isSuccessful());

        return $password;
    }

    /**
     * Returns a description of this security policy.
     *
     * @return    string
     */
    abstract public function getDescription();

    /**
     * Returns the name of this security policy.
     *
     * @return    string
     */
    abstract public function getName();

    /**
     * Returns a list of authentication factors available for security policies.
     *
     * @return    array
     */
    public static function getAuthFactorOptions()
    {
        return array(
            DSecurityPolicy::AUTH_FACTORS_ONE => new DLabel(self::class, 'authFactorsOne'),
            DSecurityPolicy::AUTH_FACTORS_TWO => new DLabel(self::class, 'authFactorsTwo'),
        );
    }

    /**
     * Returns the override condition for this policy.
     *
     * @return    int
     */
    public function getOverrideCondition()
    {
        return $this->getFieldValue(self::FIELD_OVERRIDE_CONDITION);
    }

    /**
     * Returns a list of override conditions available for security policies.
     *
     * @return    array
     */
    public static function getOverrideConditionOptions()
    {
        return array(
            DSecurityPolicy::OVERRIDE_WEAKER   => new DLabel(self::class, 'overrideWeaker'),
            DSecurityPolicy::OVERRIDE_NONE     => new DLabel(self::class, 'overrideNone'),
            DSecurityPolicy::OVERRIDE_STRONGER => new DLabel(self::class, 'overrideStronger'),
        );
    }

    /**
     * Returns a list of password strengths available for security policies.
     *
     * @return    array
     */
    public static function getPasswordStrengthOptions()
    {
        return array(
            DSecurityPolicy::PASSWORD_STRENGTH_LOW    => new DLabel(self::class, 'passwordStrengthLow'),
            DSecurityPolicy::PASSWORD_STRENGTH_MEDIUM => new DLabel(self::class, 'passwordStrengthMedium'),
            DSecurityPolicy::PASSWORD_STRENGTH_HIGH   => new DLabel(self::class, 'passwordStrengthHigh'),
        );
    }

    /**
     * Returns the session limit for this policy.
     *
     * @return    int
     */
    public function getSessionLimit()
    {
        return $this->getFieldValue(self::FIELD_SESSION_LIMIT);
    }

    /**
     * Returns the session timeout for this policy.
     *
     * @return    int
     */
    public function getSessionTimeout()
    {
        return $this->getFieldValue(self::FIELD_SESSION_TIMEOUT);
    }

    /**
     * Sets the default values for this security policy.
     *
     * @return    void
     */
    abstract protected function initialise();

    /**
     * Whether the parameters of this policy can be modified by the user.
     *
     * @return    bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Tests to see if another security policy is weaker than this security policy.
     *
     * @param    DSecurityPolicy $policy The policy to test.
     *
     * @return    bool
     */
    public function isStrongerThan(DSecurityPolicy $policy)
    {
        // Fields where a smaller value is weaker.
        $fieldNamesLesser = array(
            self::FIELD_AUTH_FACTOR_REQUIREMENT,
            self::FIELD_INACTIVE_LOCKOUT,
            self::FIELD_OVERRIDE_CONDITION,
            self::FIELD_PASSWORD_STRENGTH,
            self::FIELD_REMEMBERED_PASSWORDS,
            self::FIELD_MINIMUM_PASSWORD_LENGTH,
            self::FIELD_LOCKOUT_LENGTH,
        );
        foreach ($fieldNamesLesser as $fieldName) {
            if ($this->getFieldValue($fieldName) < $policy->getFieldValue($fieldName)) {
                return false;
            }
        }
        // Fields where a smaller value is stronger, except 0 is weaker.
        $fieldNamesGreater = array(
            self::FIELD_FAILED_LOGIN_LOCKOUT,
            self::FIELD_SESSION_TIMEOUT,
            self::FIELD_PASSWORD_LIFE,
            self::FIELD_SESSION_LIMIT,
        );
        foreach ($fieldNamesGreater as $fieldName) {
            if ($this->getFieldValue($fieldName) === 0
                || $this->getFieldValue($fieldName) > $policy->getFieldValue($fieldName)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets the session limit for this policy.
     *
     * @param    int $sessionLimit The maximum number of sessions.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setSessionLimit($sessionLimit)
    {
        $this->setFieldValue(self::FIELD_SESSION_LIMIT, $sessionLimit);

        return $this;
    }

    /**
     * Sets the override condition for this policy.
     *
     * @param    int $overrideCondition       The override condition. One of:
     *                                        - {@link DSecurityPolicy::OVERRIDE_WEAKER}
     *                                        - {@link DSecurityPolicy::OVERRIDE_NONE}
     *                                        - {@link DSecurityPolicy::OVERRIDE_STRONGER}
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     */
    public function setOverrideCondition($overrideCondition)
    {
        $this->setFieldValue(self::FIELD_OVERRIDE_CONDITION, $overrideCondition);

        return $this;
    }

    /**
     * Tests a password against this security policy.
     *
     * If the password meets the minimum requirements of the policy,
     * a successful {@link app::decibel::utility::DResult DResult} object will
     * be returned. Otherwise an unsuccessful {@link app::decibel::utility::DResult DResult}
     * object containining messages describing why the password failed the test
     * will be returned.
     *
     * @param    string $password The password to test.
     *
     * @return    DResult
     */
    public function testPassword($password)
    {
        $result = new DResult(
            new DLabel(self::class, 'passwordTest'),
            new DLabel(self::class, 'passed')
        );
        // Check length.
        $minimumPasswordLength = $this->getFieldValue(self::FIELD_MINIMUM_PASSWORD_LENGTH);
        if (strlen($password) < $minimumPasswordLength) {
            $result->setSuccess(false,
                                new DLabel(self::class, 'passwordLengthError',
                                           array('minimumPasswordLength' => $minimumPasswordLength)),
                                DUser::FIELD_PASSWORD);
        }
        // Check strength.
        $passwordStrength = $this->getFieldValue(self::FIELD_PASSWORD_STRENGTH);
        if ($passwordStrength === DSecurityPolicy::PASSWORD_STRENGTH_HIGH
            && !preg_match('/[^\pL\pN]+/', $password)
        ) {
            $result->setSuccess(false, new DLabel(self::class, 'highPasswordStrengthError'),
                                DUser::FIELD_PASSWORD);
        }
        if ($passwordStrength === DSecurityPolicy::PASSWORD_STRENGTH_MEDIUM
            && !preg_match('/[^\pL]+/', $password)
        ) {
            $result->setSuccess(false, new DLabel(self::class, 'mediumPasswordStrengthError'),
                                DUser::FIELD_PASSWORD);
        }

        return $result;
    }
}
