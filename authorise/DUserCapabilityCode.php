<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\cache\DCapabilityCache;
use app\decibel\model\event\DOnUncache;

/**
 * Provides functionality for generating user's capability code.
 *
 * @author        Timothy de Paris
 */
class DUserCapabilityCode implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Used to cache the capability code for this user after
     * first accessed by {@link DUserCapabilityCode::getCapabilityCode()}.
     *
     * @var        string
     */
    protected $capabilityCode;

    /**
     * Clears cached capability code information when a user instance is modified.
     *
     * @param    DOnUncache $event
     *
     * @return    void
     */
    public static function clearCapabilityCache(DOnUncache $event)
    {
        $user = $event->getModelInstance();
        // Clear from the memory cache.
        DCapabilityCache::load()->clearUser($user);
        // Clear from process memory if needed.
        $adapter = static::adapt($user);
        $adapter->capabilityCode = null;
    }

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
     * Returns a code that describes the capabilities of this user.
     *
     * Capabilities include Privileges and Permissions.
     *
     * This can either be an md5 hash describing shared capabilities of the
     * user (based on assigned groups), or the id of the user if personal
     * capabilities are available (ie one or more permissions are directly
     * assigned to this user.
     *
     * @return    string
     */
    public function getCapabilityCode()
    {
        /* @var $user DUser */
        $user = $this->getAdaptee();
        // Some capability codes are preset.
        if ($user->getId() === 0) {
            return 0;
        }
        if ($this->capabilityCode === null) {
            // Check in User Capability Cache.
            $capabilityCache = DCapabilityCache::load();
            $this->capabilityCode = $capabilityCache->getForUser($user);
            if ($this->capabilityCode === null) {
                $this->capabilityCode = $this->generateCapabilityCode($user);
                // Cache the capability code for next time before returning it.
                $capabilityCache->setForUser($user, $this->capabilityCode);
            }
        }

        return $this->capabilityCode;
    }

    /**
     * Generates a capability code for this user that uniquely identifies
     * capabilities (permissions and privileges) compared to other users.
     *
     * @param    DUser $user
     *
     * @return    string
     */
    protected function generateCapabilityCode(DUser $user)
    {
        // Get all permissors and order numerically by ID.
        $userGroups = DUserGroups::adapt($user);
        $capabilities = array_keys($userGroups->getGroups());
        asort($capabilities);

        // Generate shared capability code.
        return md5(serialize($capabilities));
    }
}
