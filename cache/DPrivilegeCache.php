<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\authorise\DUser;
use app\decibel\authorise\DUserCapabilityCode;
use app\decibel\cache\DCacheHandler;
use app\decibel\cache\DCapabilityAware;

/**
 * Handles the caching of user privileges.
 *
 * @author    Timothy de Paris
 */
class DPrivilegeCache extends DCacheHandler implements DCapabilityAware
{
    /**
     * Clears all information from the cache related to the specified
     * capability codes.
     *
     * This function will be called in all cache handlers whenever the
     * permissions available to a capability code are modified.
     *
     * @param    array $capabilityCodes The capability codes to clear.
     *
     * @return    bool
     */
    public function clearCapabilities(array $capabilityCodes)
    {
        // Clear from memory.
        $success = true;
        foreach ($capabilityCodes as $capabilityCode) {
            $success = $success && $this->invalidate($capabilityCode);
        }

        return $success;
    }

    /**
     * Returns a cached privilege.
     *
     * @param    string $privilege The name of the privilege.
     * @param    DUser  $user      The user.
     *
     * @return    bool    The privilege value, or <code>null</code>
     *                    if the privilege has not been cached.
     */
    public function retrieve($privilege, DUser $user)
    {
        // Build key.
        $userCapabilityCode = DUserCapabilityCode::adapt($user);
        $capabilityCode = $userCapabilityCode->getCapabilityCode();
        $key = array(
            $privilege      => false,
            $capabilityCode => true,
        );

        // Check in memory.
        return $this->getFromMemory($key);
    }

    /**
     * Caches a permission.
     *
     * @param    string $privilege Name of the privilege.
     * @param    DUser  $user      The user.
     * @param    bool   $value     The privlege value.
     *
     * @return    bool    <code>true</code> if the value was set into the cache,
     *                    <code>false</code> if not.
     */
    public function set($privilege, DUser $user, $value)
    {
        // Build key.
        $userCapabilityCode = DUserCapabilityCode::adapt($user);
        $capabilityCode = $userCapabilityCode->getCapabilityCode();
        $key = array(
            $privilege      => false,
            $capabilityCode => true,
        );

        // Store in memory.
        return $this->storeInMemory($key, $value);
    }
}
