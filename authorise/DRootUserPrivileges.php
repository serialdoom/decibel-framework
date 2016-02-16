<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\authorise\debug\DInvalidPrivilegeException;

/**
 * Provides functionality for managing {@link DRootUser} privileges.
 *
 * @author        Timothy de Paris
 */
class DRootUserPrivileges extends DUserPrivileges
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DRootUser::class;
    }

    /**
     * Returns a list of privileges that this user has been granted.
     *
     * @return    array
     */
    public function getPrivileges()
    {
        return DPrivilege::getPrivilegeInformation();
    }

    /**
     * Temporarily grants a privilege to this user.
     *
     * @note
     * This method has no effect as the root user already has all privileges.
     *
     * @param    string $privilege The privilege to grant.
     *
     * @return    void
     */
    public function grantPrivilege($privilege)
    {
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

        return true;
    }
}
