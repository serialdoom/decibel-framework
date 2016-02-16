<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

/**
 * The {@link DCapabilityAware} interface should be implemented by any cache
 * handler that relies on capability codes to accurately store and retrieve
 * cached information.
 *
 * @author        Timothy de Paris
 */
interface DCapabilityAware
{
    /**
     * Clears all information from the cache related to the specified
     * capability codes.
     *
     * This function will be called in all cache handlers whenever the
     * permissions available to a capability code are modified.
     *
     * @param    array $capabilityCodes The capbility codes to clear.
     *
     * @return    void
     */
    public function clearCapabilities(array $capabilityCodes);
}
