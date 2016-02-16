<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\configuration\DConfiguration;

/**
 * Configuration for the security policies for a Decibel application.
 *
 * @author    Timothy de Paris
 */
class DSecurityPolicies extends DConfiguration
{
    /**
     * Default security policy key.
     *
     * @var        string
     */
    const DEFAULT_POLICY = 'default';
    /**
     * Currently configured security policies.
     *
     * @var        array
     */
    protected $policies = array();

    /**
     * Specifies which fields will be stored in the serialized object.
     *
     * @return    array    List containing names of the fields to serialize.
     */
    public function __sleep()
    {
        return array('policies');
    }

    /**
     * Clears a security policy.
     *
     * @param    string $key              Policy key. This can be
     *                                    {@link DSecurityPolicies::DEFAULT_POLICY}
     *                                    to clear the default security policy. Other keys
     *                                    may be used if security policies are used
     *                                    for other purposes by an application.
     * @param    string $owner            Qualified name of the class setting the policy.
     *                                    This parameter is only required if namespacing
     *                                    of security policies is required (for example,
     *                                    overlapping keys need to be utilised).
     *
     * @return    void
     */
    public function clearPolicy($key, $owner = self::class)
    {
        unset($this->policies[ $owner ][ $key ]);
    }

    /**
     * Defines fields available for this configuration.
     *
     * @return    void
     */
    public function define()
    {
    }

    /**
     * Retrieves a security policy.
     *
     * @param    string $key      Policy key. This can be one of
     *                            {@link DSecurityPolicies::DEFAULT_POLICY}
     *                            to retrieve the default security policy. Other
     *                            keys may be used if security policies are used
     *                            for other purposes by an application.
     * @param    string $owner    Qualified name of the class setting the policy.
     *                            This parameter is only required if namespacing
     *                            of security policies is required (for example,
     *                            overlapping keys need to be utilised).
     *
     * @return    DSecurityPolicy    The defined policy, or <code>null</code>
     *                            if no policy has been defined.
     */
    public function getPolicy($key, $owner = self::class)
    {
        if (isset($this->policies[ $owner ][ $key ])) {
            $policy = $this->policies[ $owner ][ $key ];
        } else {
            if ($owner === self::class) {
                $policy = new DDefaultSecurityPolicy();
            } else {
                $policy = null;
            }
        }

        return $policy;
    }

    /**
     * Returns the default security policy for this Decibel installation.
     *
     * @return    DSecurityPolicy
     */
    public function getDefaultPolicy()
    {
        return $this->getPolicy(self::DEFAULT_POLICY);
    }

    /**
     * Sets a security policy.
     *
     * @param    string          $key     Policy key. This can be one of
     *                                    {@link DSecurityPolicies::DEFAULT_POLICY}
     *                                    to set the default security policies. Other keys
     *                                    may be used if security policies are used
     *                                    for other purposes by an application.
     * @param    DSecurityPolicy $policy  The policy to use for the specified key.
     * @param    string          $owner   Qualified name of the class setting the policy.
     *                                    This parameter is only required if namespacing
     *                                    of security policies is required (for example,
     *                                    overlapping keys need to be utilised).
     *
     * @return    void
     */
    public function setPolicy($key, DSecurityPolicy $policy,
                              $owner = self::class)
    {
        $this->policies[ $owner ][ $key ] = $policy;
    }
}
