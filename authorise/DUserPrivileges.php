<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\authorise\debug\DInvalidPrivilegeException;
use app\decibel\cache\DPrivilegeCache;

/**
 * Provides functionality for managing a user's privileges.
 *
 * @author        Timothy de Paris
 */
class DUserPrivileges implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Internal cache for privileges.
     *
     * @var        array
     */
    private $privilegeCache = array();

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
     * Returns a list of privileges that this user has been granted.
     *
     * @return    array
     */
    public function getPrivileges()
    {
        $userPrivileges = array();
        foreach (DPrivilege::getPrivilegeInformation() as $privilege) {
            /* @var $privilege DPrivilege */
            if ($this->hasPrivilege($privilege->getName())) {
                $userPrivileges[] = $privilege;
            }
        }

        return $userPrivileges;
    }

    /**
     * Temporarily grants a privilege to this user.
     *
     * @note
     * The grant only lasts for the current request, or until this model
     * is uncached.
     *
     * @param    string $privilege The privilege to grant.
     *
     * @return    void
     */
    public function grantPrivilege($privilege)
    {
        $this->privilegeCache[ $privilege ] = true;
    }

    /**
     * Determines if this user has a specified privilege.
     *
     * Privileges are inherited from the Groups a user is assigned to.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    bool
     * @throws    DInvalidPrivilegeException    If the specified privilege does not exist.
     */
    public function hasPrivilege($privilege)
    {
        // Check to see if the privilege is valid.
        if (!DPrivilege::isValid($privilege)) {
            throw new DInvalidPrivilegeException($privilege);
        }
        // Handle blank privilege.
        if (empty($privilege)) {
            $hasPrivilege = true;
        } else {
            // Check the cache first.
            $cachedPrivilege = $this->hasPrivilegeFromCache($privilege);
            if ($cachedPrivilege !== null) {
                $hasPrivilege = $cachedPrivilege;
                // Check the profile and groups for the privilege.
            } else {
                $hasPrivilege = $this->hasPrivilegeFromProfile($privilege)
                    || $this->hasPrivilegeFromGroups($privilege);
                // Cache for next time.
                $privilegeCache = DPrivilegeCache::load();
                $privilegeCache->set($privilege, $this->adaptee, $hasPrivilege);
                $this->privilegeCache[ $privilege ] = $hasPrivilege;
            }
        }

        return $hasPrivilege;
    }

    /**
     * Checks whether information about this privilege is cached.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    bool
     */
    protected function hasPrivilegeFromCache($privilege)
    {
        if ($this->adaptee->hasUnsavedChanges()) {
            $hasPrivilege = null;
            // Check in internal cache.
        } else {
            if (isset($this->privilegeCache[ $privilege ])) {
                $hasPrivilege = $this->privilegeCache[ $privilege ];
                // Check the privilege cache.
            } else {
                $privilegeCache = DPrivilegeCache::load();
                $hasPrivilege = $privilegeCache->retrieve($privilege, $this->adaptee);
                if ($hasPrivilege !== null) {
                    $this->privilegeCache[ $privilege ] = $hasPrivilege;
                }
            }
        }

        return $hasPrivilege;
    }

    /**
     * Checks whether the user is granted a privilege via their groups.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    bool
     */
    protected function hasPrivilegeFromGroups($privilege)
    {
        $hasPrivilege = false;
        $userGroups = DUserGroups::adapt($this->adaptee);
        foreach ($userGroups->getGroups() as $group) {
            /* @var $group DGroup */
            if ($group->hasPrivilege($privilege)) {
                $hasPrivilege = true;
                break;
            }
        }

        return $hasPrivilege;
    }

    /**
     * Checks whether the user is granted a privilege via their profile.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    bool
     */
    protected function hasPrivilegeFromProfile($privilege)
    {
        $profile = $this->adaptee->getFieldValue(DUser::FIELD_PROFILE);

        return ($profile
            && $profile->hasPrivilege($privilege));
    }
}
