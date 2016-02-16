<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\authorise\DPrivilege;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DRegistryHive;

/**
 * Registers information about model privileges.
 *
 * @author        Timothy de Paris
 */
class DPrivilegeInformation extends DRegistryHive
{
    /**
     * Index of privileges within the scope of the registry.
     *
     * @var        array
     */
    protected $privileges = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array_merge(
            parent::generateDebug(),
            array(
                'privileges' => $this->privileges,
            )
        );
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'privileges';

        return $sleep;
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);

        return $fileInformation->getChecksum();
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(
            DFileInformation::class,
            DClassInformation::class,
        );
    }

    /**
     * Returns a version number indicating the format of the registry.
     *
     * @return    int
     */
    public function getFormatVersion()
    {
        return 1;
    }

    /**
     * Returns the privileges within the scope of this registry.
     *
     * @return    array
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool
     */
    public function merge(DRegistryHive $hive)
    {
        if ($hive instanceof DPrivilegeInformation) {
            $this->privileges = array_merge(
                $this->privileges,
                $hive->privileges
            );
            $merged = true;
        } else {
            $merged = false;
        }

        return $merged;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        $models = $this->getDependency(DClassInformation::class)
                       ->getClassNames(DBaseModel::class);
        $this->privileges = array();
        foreach ($models as $qualifiedName) {
            // Determine the edit privilege for this model.
            $instance = $qualifiedName::create();
            $privilege = $instance->getPrivilegeName(DPrivilege::SUFFIX_EDIT);
            $instance->free();
            if ($privilege) {
                $this->privileges[] = $privilege;
            }
        }
    }
}
