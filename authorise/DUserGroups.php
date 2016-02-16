<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;

/**
 * Provides functionality for managing a user's groups.
 *
 * @author        Timothy de Paris
 */
class DUserGroups implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Groups this user is assigned to, cached
     * by the {@link DUserGroups::getGroups()} function.
     *
     * @var        array
     */
    private $groupPointers;

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
     * Returns an array containing each of the groups this user belongs to.
     *
     * @return    array
     */
    public function getGroups()
    {
        if (!$this->groupPointers
            && !$this->adaptee->hasUnsavedChanges()
        ) {
            $this->groupPointers = array();
            foreach (DGroup::search() as $group) {
                /* @var $group DGroup */
                if ($this->isGroupMember($group)) {
                    $this->groupPointers[ $group->getId() ] = $group;
                } else {
                    $group->free();
                }
            }
        }

        return $this->groupPointers;
    }

    ///@cond INTERNAL
    /**
     *
     * @todo    Remove this function
     * @deprecated
     */
    public function getGroupPointers()
    {
        if ($this->groupPointers === null) {
            $pointers = array();
        } else {
            $pointers = $this->groupPointers;
        }

        return $pointers;
    }
    ///@endcond
    /**
     * Determines if this user is a member of the specified group.
     *
     * @param    DGroup $group The group to test.
     *
     * @return    bool
     */
    public function isGroupMember(DGroup $group)
    {
        $groups = $this->adaptee->getFieldValue(DUser::FIELD_GROUPS);

        return in_array($group, $groups);
    }
}
