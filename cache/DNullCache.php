<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\cache\DCache;
use app\decibel\regional\DLabel;
use app\decibel\service\DServiceContainer;
use app\decibel\utility\DResult;

/**
 * This fallback memory cache handler will be loaded where the selected
 * memory cache is not available.
 *
 * No caching is performed by this class, it simply allows normal application
 * functionality where an issue exists with the memory cache.
 *
 * @author    Timothy de Paris
 */
class DNullCache extends DCache
{
    /**
     * Initialises any required functionality for the cache.
     *
     * @return    void
     */
    protected function initialise()
    { }

    /**
     * Determines if the application or functionality required by this wrapper
     * if currently available.
     *
     * @return    bool
     */
    public static function isAvailable()
    {
        return false;
    }

    /**
     * Determines if this functionality is required by decibel.
     *
     * @return    bool
     */
    public static function isRequired()
    {
        return false;
    }

    /**
     * Returns the qualified name of the {@link app::decibel::configuration::DConfiguration DConfiguration} class used
     * to configure this object.
     *
     * @return    string    The qualified name of the {@link app::decibel::configuration::DConfiguration
     *                      DConfiguration} class for this object, or null if no configuration is available.
     */
    public static function getConfigurationClass()
    {
        return null;
    }

    /**
     * Returns a human-readable description for the utility.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        return new DLabel(self::class, 'description');
    }

    /**
     * Returns a human-readable name for the utility.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return new DLabel(self::class, 'displayName');
    }

    /**
     * Determines if this cache wrapper can support cache clustering.
     *
     * If the application is running in clustered mode, the selected cache type
     * should also support clustering.
     *
     * @return    bool
     */
    public function supportsClustering()
    {
        return true;
    }

    /**
     * Checks if all functionality required to use the service
     * is currently available.
     *
     * @return    DResult
     */
    public function test()
    {
        return new DResult(
            static::getDisplayName(),
            new DLabel(DServiceContainer::class, 'tested'),
            false
        );
    }

    /**
     * Retrieves information from the cache.
     *
     * @param    string $key The key of the information to retrieve.
     *
     * @return    mixed    The requested information, or <code>null</code>
     *                    if no value was found.
     */
    protected function getValue($key)
    {
        return null;
    }

    /**
     * Stores information in the cache.
     *
     * @param    string $key          The key to store the information against.
     * @param    mixed  $value        The value to store.
     * @param    int    $expiry       The timestamp at which the cached information
     *                                will expire. If omitted or zero, the information
     *                                will be cached indefinitely.
     *
     * @return    bool    <code>true</code> if the information was stored
     *                    successfully, otherwise <code>false</code>.
     */
    protected function setValue($key, $value, $expiry = 0)
    {
        return false;
    }

    /**
     * Removes information from the cache.
     *
     * @param    string $key The key of the information to remove.
     *
     * @return    bool    <code>true</code> if the information was successfully
     *                    removed, <code>false</code> otherwise.
     */
    protected function removeValue($key)
    {
        return false;
    }
}
