<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\cache\DPublicCache;

/**
 * Allows field definitions for {@link DDefinable} objects to be cached in process memory.
 *
 * @author        Timothy de Paris
 */
trait DDefinableCache
{
    /**
     * Definitions of fields available for currently loaded utility data objects.
     *
     * @var        array
     */
    private static $fieldDefinitions;

    /**
     * Loads field definitions for this object from process memory, shared memory cache
     * or by calling the {@link DDefinable::define()} method.
     *
     * @return    void
     */
    protected function loadDefinitions()
    {
        $qualifiedName = get_called_class();
        // Fields have been loaded in this request.
        if (isset(self::$fieldDefinitions[ $qualifiedName ])) {
            $this->fields =& self::$fieldDefinitions[ $qualifiedName ];
        } else {
            // Otherwise, check if they have been cached.
            // There is the possibility of recursion here, as the configuration
            // manager loads DConfiguration objects (which extend DUtilityData)
            // however the cache requires configuration to be loaded.
            // @todo Try removing this after cleaning up configuration options.
            if (DPublicCache::isLoading()) {
                $publicCache = null;
                $fields = null;
            } else {
                $publicCache = DPublicCache::load();
                $fields = $publicCache->retrieve(__CLASS__, $qualifiedName);
            }
            if ($fields !== null) {
                self::$fieldDefinitions[ $qualifiedName ] =& $fields;
                $this->fields =& $fields;
                // If not, we'll have to build them from scratch.
            } else {
                self::$fieldDefinitions[ $qualifiedName ] = array();
                $this->fields =& self::$fieldDefinitions[ $qualifiedName ];
                $this->define();
                // Cache for next time.
                if ($publicCache) {
                    $publicCache->set(__CLASS__, $qualifiedName, $this->fields);
                }
            }
        }
    }
}
