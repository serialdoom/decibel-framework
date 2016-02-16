<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\authorise;

use app\decibel\application\DAppManager;
use app\decibel\model\DPrivilegeInformation;
use app\decibel\registry\DGlobalRegistry;

/**
 * Provides functionality for working with privileges.
 *
 * Privileges control an area of the application that only certain users
 * are able to access.
 *
 * @author        Timothy de Paris
 */
final class DPrivilege
{
    /**
     * Standard 'Create' privilege suffix.
     *
     * @var        string
     */
    const SUFFIX_CREATE = 'Create';
    /**
     * Standard 'Delete' privilege suffix.
     *
     * @var        string
     */
    const SUFFIX_DELETE = 'Delete';
    /**
     * Standard 'Edit' privilege suffix.
     *
     * @var        string
     */
    const SUFFIX_EDIT = 'Edit';

    /**
     * Privilege registration type.
     *
     * @var        string
     */
    const REGISTRATION_PRIVILEGE = 'privilege';
    /**
     * System ID (and name) of the Root privilege.
     *
     * @var        string
     */
    const ROOT = 'Root';
    /**
     * The privilege name.
     *
     * Privilege names should be in the format
     * <code>[Qualified Name]-[Action]</code>.
     *
     * @var        string
     */
    protected $name;
    /**
     * The group to which the privilege belongs.
     *
     * @var        string
     */
    protected $group;
    /**
     * Description of the privilege.
     *
     * @var        string
     */
    protected $description;

    /**
     * Creates a new DPrivilege.
     *
     * @param    string $name        The privilege name.
     * @param    string $group       The group to which the privilege belongs.
     * @param    string $description Description of the privilege.
     *
     * @return    static
     */
    public function __construct($name, $group = null, $description = null)
    {
        $this->name = $name;
        $this->group = $group;
        $this->description = $description;
    }

    /**
     * Returns a string representation of the privilge.
     *
     * @return    string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns the group of the privilege.
     *
     * @return    string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Returns the description of the privilege.
     *
     * @return    string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the name of the privilege.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Determines if this is a valid privilege.
     *
     * This is case sensitive for privilege names.
     *
     * @param    string $privilege Name of the privilege to check for.
     *
     * @return    bool
     */
    public static function isValid($privilege)
    {
        // Handle blank privileges.
        if (empty($privilege)) {
            $valid = true;
        } else {
            if ($privilege === self::ROOT) {
                $valid = true;
            } else {
                $registration = DAppManager::getRegistration(
                    get_class(),
                    self::REGISTRATION_PRIVILEGE,
                    $privilege
                );
                $valid = ($registration !== null);
            }
        }

        return $valid;
    }

    /**
     * Registers any required privileges that have not been defined by the
     * App information files.
     *
     * This function is called by DAppManager.
     *
     * @param    array $privileges Pointer to privileges in the registrations array.
     *
     * @return    void
     */
    public static function registerMissingPrivileges(array &$privileges)
    {
        $globalRegistry = DGlobalRegistry::load();
        $privilegeInformation = $globalRegistry->getHive(DPrivilegeInformation::class);
        $availablePrivileges = $privilegeInformation->getPrivileges();
        foreach ($availablePrivileges as $privilege) {
            if (!isset($privileges[ $privilege ])) {
                $privileges[ $privilege ] = new DPrivilege($privilege);
            }
        }
    }

    /**
     * Registers a privilege.
     *
     * @param    string $name        Name of the privilege.
     * @param    string $group       Group that the privilege belongs to.
     * @param    string $description Description of the privilege.
     *
     * @return    void
     */
    public static function registerPrivilege($name, $group = 'General', $description = null)
    {
        DAppManager::addRegistration(
            get_class(),
            self::REGISTRATION_PRIVILEGE,
            new DPrivilege($name, $group, $description),
            $name
        );
    }

    /**
     * Returns information about available privileges.
     *
     * @return    array
     */
    public static function getPrivilegeInformation()
    {
        return DAppManager::getRegistration(
            get_class(),
            self::REGISTRATION_PRIVILEGE
        );
    }

    /**
     * Returns information about available privileges.
     *
     * @return    array
     */
    public static function getPrivilegeNames()
    {
        return array_keys(self::getPrivilegeInformation());
    }
}
