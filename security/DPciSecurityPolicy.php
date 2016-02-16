<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\regional\DLabel;
use app\decibel\security\DSecurityPolicy;

/**
 * Defines a set of PCI DSS 2.0 compliant security attributes
 * to be applied by Decibel.
 *
 * @section        why Why Would I Use It?
 *
 * Security policies are used to enforce certain security related attributes
 * for user authentication and authorisation. This policy applies a set of
 * PCI DSS 2.0 compliant attributes.
 *
 * @section        how How Do I Use It?
 *
 * Security policies can be configured using the {@link DSecurityPolicies}
 * configuration hive.
 *
 * @subsection     example Example
 *
 * Programmatically setting the default security policy using
 * the {@link DSecurityPolicies::setPolicy} function:
 *
 * @code
 * use app\decibel\security\DSecurityPolicies;
 *
 * $securityPolicies = DSecurityPolicies::load();
 * $securityPolicies->setPolicy(
 *    DSecurityPolicies::DEFAULT_POLICY,
 *    new DPciSecurityPolicy()
 * );
 * $securityPolicies->save();
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        security
 */
class DPciSecurityPolicy extends DSecurityPolicy
{
    /**
     * Returns a description of this security policy.
     *
     * @return    DLabel
     */
    public function getDescription()
    {
        return new DLabel(self::class, 'description');
    }

    /**
     * Returns the name of this security policy.
     *
     * @return    DLabel
     */
    public function getName()
    {
        return new DLabel(self::class, 'name');
    }

    /**
     * Sets the default values for this security policy.
     *
     * @return    void
     */
    protected function initialise()
    {
        parent::setFieldValue(self::FIELD_AUTH_FACTOR_REQUIREMENT, self::AUTH_FACTORS_TWO);
        parent::setFieldValue(self::FIELD_FAILED_LOGIN_LOCKOUT, 6);
        parent::setFieldValue(self::FIELD_INACTIVE_LOCKOUT, 90);
        parent::setFieldValue(self::FIELD_LOCKOUT_LENGTH, 30);
        parent::setFieldValue(self::FIELD_SESSION_LIMIT, 1);
        parent::setFieldValue(self::FIELD_MINIMUM_PASSWORD_LENGTH, 8);
        parent::setFieldValue(self::FIELD_PASSWORD_LIFE, 90);
        parent::setFieldValue(self::FIELD_REMEMBERED_PASSWORDS, 5);
        parent::setFieldValue(self::FIELD_SESSION_TIMEOUT, 15);
    }

    /**
     * Whether the parameters of this policy can be modified by the user.
     *
     * @return    bool
     */
    public function isConfigurable()
    {
        return false;
    }

    /**
     * Sets the value for a specified field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    string $fieldName Name of the field to set the value for.
     * @param    mixed  $value     The value to set.
     *
     * @return    void
     * @throws    DReadOnlyParameterException    If the value for this cannot
     *                                        be changed.
     */
    public function setFieldValue($fieldName, $value)
    {
        throw new DReadOnlyParameterException($fieldName, __CLASS__);
    }
}
