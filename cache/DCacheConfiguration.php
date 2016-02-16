<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\configuration\DConfiguration;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\registry\DClassQuery;

/**
 * Configuration class for caching options.
 *
 * @author        Timothy de Paris
 */
class DCacheConfiguration extends DConfiguration
{
    /**
     * Qualified name of the selected shared memory cache for this Decibel installation.
     *
     * @var        string
     */
    protected $cache = DNullCache::class;

    /**
     * Specifies which fields will be stored in the serialized object.
     *
     * @return    array    List containing names of the fields to serialize.
     */
    public function __sleep()
    {
        return array('cache');
    }

    /**
     * Defines the fields available for this configuration.
     *
     * @return    void
     */
    protected function define()
    { }

    /**
     * Returns the qualified name of the cache to be used by this Decibel installation.
     *
     * @note
     * The configured cache will be validated to ensure that it is still available.
     * In the case of a third-party cache having been uninstalled since the configuration
     * was set, the {@link DDatabaseCache} will be returned.
     *
     * @return    string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Determines if the specified class name is a valid cache.
     *
     * @param    string $qualifiedName
     *
     * @return    bool
     */
    public static function isValidCache($qualifiedName)
    {
        return DClassQuery::load()
                          ->setAncestor(DCache::class)
                          ->isValid($qualifiedName);
    }

    /**
     * Sets the cache to be used by this Decibel installation.
     *
     * @param    string $qualifiedName Qualified name of a class extending {@link DCache}.
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If an invalid class name if provided.
     */
    public function setCache($qualifiedName)
    {
        if (!static::isValidCache($qualifiedName)) {
            throw new DInvalidParameterValueException(
                'qualifiedName',
                array(__CLASS__, __FUNCTION__),
                'Qualified name of a class that extends app\decibel\cache\DCache'
            );
        }
        $this->cache = $qualifiedName;
    }
}
