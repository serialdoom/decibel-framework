<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

/**
 * Provides functionality for managing {@link DGuestUser} groups.
 *
 * @author        Timothy de Paris
 */
class DGuestUserGroups extends DUserGroups
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DGuestUser::class;
    }

    /**
     * Returns an array containing each of the groups this user belongs to.
     *
     * @return    array
     */
    public function getGroups()
    {
        return array();
    }

    /**
     * Determines if this user is a member of the specified group.
     *
     * @param    DGroup $group The group to test.
     *
     * @return    bool
     */
    public function isGroupMember(DGroup $group)
    {
        return false;
    }
}
