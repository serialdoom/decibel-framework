<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\cache\DCache;
use app\decibel\cache\debug\DKeyTooLongException;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\database\DQuery;
use app\decibel\debug\DErrorHandler;
use app\decibel\regional\DLabel;
use app\decibel\service\DServiceContainer;
use app\decibel\utility\DResult;

/**
 * Handles caching in the application database.
 *
 * @author    Timothy de Paris
 * @todo      Handle clearing of expired items and invalidation.
 */
class DDatabaseCache extends DCache
{
    /**
     * Determines if the application or functionality required by this wrapper
     * if currently available.
     *
     * @return    bool
     */
    public static function isAvailable()
    {
        return true;
    }

    /**
     * Determines if this functionality is required by Decibel.
     *
     * @return    bool
     */
    public static function isRequired()
    {
        return false;
    }

    /**
     * Returns the qualified name of the {@link app::decibel::configuration::DConfiguration DConfiguration}
     * class used to configure this object.
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
        return new DLabel('app\\decibel\\cache\\DDatabaseCache', 'description');
    }

    /**
     * Returns a human-readable name for the utility.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return new DLabel('app\\decibel\\cache\\DDatabaseCache', 'displayName');
    }

    /**
     * Initialises any required functionality for the cache.
     *
     * @return    void
     */
    protected function initialise()
    {
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
            new DLabel(DServiceContainer::class, 'tested')
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
        try {
            $query = new DQuery(
                "SELECT `value` FROM `decibel_cache_ddatabasecache` WHERE `key`='#key#' AND (`expiry`=0 OR `expiry`>#timestamp#)",
                array(
                    'key'       => $key,
                    'timestamp' => time(),
                )
            );
            if ($query->getNumRows() === 0) {
                $value = null;
            } else {
                $value = unserialize(
                    $query->get('value')
                );
            }
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $value = null;
        }

        return $value;
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
     * @throws    DKeyTooLongException    If the provided key exceeds the maximum
     *                                    allowed length of 250 characters.
     */
    protected function setValue($key, $value, $expiry = 0)
    {
        if (strlen($key) > 250) {
            throw new DKeyTooLongException($key, 250);
        }
        try {
            $query = new DQuery(
                "REPLACE INTO `decibel_cache_ddatabasecache` SET `key`='#key#', `value`='#value#', `expiry`=#expiry#",
                array(
                    'key'    => $key,
                    'value'  => serialize($value),
                    'expiry' => (int)$expiry,
                )
            );
            $result = $query->isSuccessful();
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $result = false;
        }

        return $result;
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
        try {
            $query = new DQuery(
                "DELETE FROM `decibel_cache_ddatabasecache` WHERE `key`='#key#'",
                array(
                    'key' => $key,
                )
            );
            $result = $query->isSuccessful();
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $result = false;
        }

        return $result;
    }
}
