<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\authorise\debug\DInvalidPrivilegeException;
use app\decibel\model\DModel;
use app\decibel\model\search\DModelSearch;
use app\decibel\registry\DClassQuery;
use app\decibel\security\DSecurityPolicies;
use app\decibel\security\DSecurityPolicy;

/**
 * Defines a user profile containing system configuration options
 * and default groups.
 *
 * @author        Timothy de Paris
 */
class DProfile extends DModel
{
    /**
     * 'Groups' field name.
     *
     * @var        string
     */
    const FIELD_GROUPS = 'groups';
    /**
     * 'User Object' field name.
     *
     * @var        string
     */
    const FIELD_USER_OBJECT = 'userObject';

    /**
     * Returns the security policy for this profile.
     *
     * This will be the default security profile for this type of profile unless
     * it has been overriden.
     *
     * @return    DSecurityPolicy
     */
    public function getSecurityPolicy()
    {
        $securityPolicies = DSecurityPolicies::load();

        return $securityPolicies->getDefaultPolicy();
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    protected function getStringValue()
    {
        return $this->getFieldValue('name');
    }

    /**
     * Returns an array containing registered User objects
     * for enumerated selectors.
     *
     * @return    array
     */
    public static function getUserClasses()
    {
        $available = DClassQuery::load()
                                ->setAncestor(DUser::class)
                                ->getClassNames();
        foreach ($available as $qualifiedName) {
            $userClasses[ $qualifiedName ] = $qualifiedName::getDisplayName();
        }
        $userClasses[ DUser::class ] = 'Any User';

        return $userClasses;
    }

    /**
     * Returns true if the given privileged is assigned to this profile.
     *
     * @param    mixed $privilege Name of the privilege to check for.
     *
     * @return    bool    true if the user has the privilege, false otherwise.
     * @throws    DInvalidPrivilegeException    If the specified privilege does not exist.
     */
    public function hasPrivilege($privilege)
    {
        // Check if the privilege is valid.
        if (!DPrivilege::isValid($privilege)) {
            throw new DInvalidPrivilegeException($privilege);
        }
        // Check the group privileges.
        $hasPrivilege = false;
        foreach ($this->getFieldValue(self::FIELD_GROUPS) as $group) {
            /* @var $group DGroup */
            if ($group->hasPrivilege($privilege)) {
                $hasPrivilege = true;
                break;
            }
        }

        return $hasPrivilege;
    }

    /**
     * Returns true if the given group is assigned to this profile.
     *
     * @param    DGroup $group The group to test.
     *
     * @return    boolean
     */
    public function isGroupMember(DGroup $group)
    {
        return in_array($group, $this->getFieldValue(self::FIELD_GROUPS));
    }

    /**
     * Returns a {@link app::decibel::model::search::DModelSearch DModelSearch} that can be used to generate the list of
     * available objects for linking to by {@link app::decibel::model::field::DRelationalField DRelationalField} fields.
     *
     * @param    array $options Additional options for the search.
     *
     * @return    DModelSearch
     */
    public static function link($options = array())
    {
        $search = self::search()
                      ->sortByField(self::FIELD_STRING_VALUE)
                      ->includeFields(array(
                                          self::FIELD_STRING_VALUE,
                                      ));
        // Filter to profiles for a particular type of user object.
        if (isset($options[ self::FIELD_USER_OBJECT ])) {
            $search->filterByField(self::FIELD_USER_OBJECT, $options['userObject']);
        }

        return $search;
    }
}
