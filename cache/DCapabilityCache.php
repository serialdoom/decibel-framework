<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\authorise\DUser;
use app\decibel\authorise\DUserCapabilityCode;
use app\decibel\cache\DCacheHandler;

/**
 * Provides caching for Capability Codes.
 *
 * A User's Capability Code is an MD5 hash of their assigned Permissors,
 * unless the User has been personally assigned one or more permissions,
 * in which case their Capability Code is their ID.
 *
 * @author        Timothy de Paris
 */
class DCapabilityCache extends DCacheHandler
{
    /**
     * Clears all information from the Capability Cache for the specified user.
     *
     * @param    DUser $user The user to clear the cache for.
     *
     * @return    bool
     */
    public function clearUser(DUser $user)
    {
        // If the user has been directly assigned to a permissible object
        // as a permissor, the capability code will be the User's ID. In this
        // case, only removing the capability code will not be enough and
        // all permissions for this user must be cleared.
        $userId = $user->getId();
        $userCapabilityCode = DUserCapabilityCode::adapt($user);
        $capabilityCode = $userCapabilityCode->getCapabilityCode();
        if ($capabilityCode === $userId) {
            DCacheHandler::clearCapabilitiesCaches(array($capabilityCode));
        }
        // Determine cache key.
        $key = $userId;

        // Clear from memory.
        return $this->removeFromMemory($key);
    }

    /**
     * Returns a cached capability code for the specified User.
     *
     * @param    DUser $user The User to retrieve a Capability Code for.
     *
     * @return    string    The capability code, or null if no code is found for the user.
     */
    public function getForUser(DUser $user)
    {
        // Determine cache key.
        $key = $user->getId();

        // Check in memory.
        return $this->getFromMemory($key);
    }

    /**
     * Caches a capability code for a user.
     *
     * @param    DUser  $user The User for which the Capability Code is being cached.
     * @param    string $code The Capability Code.
     *
     * @return    bool
     */
    public function setForUser(DUser $user, $code)
    {
        // Don't cache capability codes for new users.
        $userId = $user->getId();
        if ($userId === 0) {
            $success = false;
        } else {
            // Determine cache key.
            $key = $userId;
            // Store in memory.
            $success = $this->storeInMemory($key, $code, false);
        }

        return $success;
    }
}
