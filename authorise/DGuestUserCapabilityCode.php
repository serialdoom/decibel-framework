<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

/**
 * Provides functionality for generating {@link DGuestUser} capability code.
 *
 * @author        Timothy de Paris
 */
class DGuestUserCapabilityCode extends DUserCapabilityCode
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
        return DGuestUser::class;
    }
}
