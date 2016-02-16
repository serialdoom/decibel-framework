<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\regional\DLabel;
use app\decibel\security\DSecurityPolicy;

/**
 * Defines the default security attributes applied by Decibel.
 *
 * @author        Timothy de Paris
 */
class DDefaultSecurityPolicy extends DSecurityPolicy
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
    { }

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
