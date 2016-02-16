<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\authorise\debug\DInvalidPrivilegeException;
use app\decibel\model\DModel;
use app\decibel\model\event\DModelEvent;
use app\decibel\model\search\DFieldCondition;
use app\decibel\model\search\DOrCondition;

/**
 * Provides a base for the user management system of Decibel allowing users
 * to be placed in groups with assigned privileges.
 *
 * @author        Timothy de Paris
 */
class DGroup extends DModel
{
    /**
     * 'Name' field name.
     *
     * @var        string
     */
    const FIELD_NAME = 'name';
    /**
     * 'Parent' field name.
     *
     * @var        string
     */
    const FIELD_PARENT = 'parent';

    /**
     * Performs any uncaching operations neccessary when a model's data is changed to ensure
     * consitency across the application.
     *
     * @param    DModelEvent $event The event that required uncaching of the model.
     *
     * @return    void
     */
    public function uncache(DModelEvent $event = null)
    {
        parent::uncache($event);
        // Remove users that belong to this group from the cache.
        $profiles = DProfile::search()
                            ->filterByField(DProfile::FIELD_GROUPS, $this)
                            ->getIds();
        $users = DUser::search()
                      ->addCondition(new DOrCondition(
                                         new DFieldCondition(DUser::FIELD_GROUPS, $this),
                                         new DFieldCondition(DUser::FIELD_PROFILE, $profiles)
                                     ));
        foreach ($users as $user) {
            /* @var $user DUser */
            $user->uncache();
            $user->free();
        }
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    protected function getStringValue()
    {
        return $this->getFieldValue(self::FIELD_NAME);
    }

    /**
     * Returns true if the given privileged is assigned to this group.
     * A group is also considered to have a privilege if a group it
     * extends has that privilege.
     *
     * @param    string $privilege The name of the privilege to check for.
     *
     * @return    boolean
     * @throws    DInvalidPrivilegeException    If the specified privilege does not exist.
     */
    public function hasPrivilege($privilege)
    {
        // Check to see if the privilege is valid.
        if (!DPrivilege::isValid($privilege)) {
            throw new DInvalidPrivilegeException($privilege);
        }

        // Check this group's privileges.
        return in_array($privilege, $this->privileges);
    }
}
