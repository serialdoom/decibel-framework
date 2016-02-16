<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\cache\DCacheHandler;
use app\decibel\cache\DCapabilityAware;
use app\decibel\model\DBaseModel;

/**
 * Provides caching for DModelSearch.
 *
 * @author    Nikolay Dimitrov
 */
class DModelSearchCache extends DCacheHandler implements DCapabilityAware
{
    /**
     * Used to track keys that have been removed, to avoid trying this
     * twice in a single page load.
     *
     * @var        array
     */
    protected $removed = array();

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
    public function clearCapabilities(array $capabilityCodes)
    {
        // Clear from memory.
        foreach ($capabilityCodes as $capabilityCode) {
            $this->invalidate($capabilityCode);
        }
    }

    /**
     * Clears all information from the cache related to the specified
     * capability codes.
     *
     * @param    string $qualifiedName Qualified name of the model to clear.
     *
     * @return    bool
     */
    public function clearQualifiedName($qualifiedName)
    {
        // Discover parent model and clear this as well.
        $parentQualifiedName = get_parent_class($qualifiedName);
        if ($parentQualifiedName
            && is_subclass_of($parentQualifiedName, DBaseModel::class)
        ) {
            $this->clearQualifiedName($parentQualifiedName);
        }
        // Track what's being removed so that we can avoid removing it more
        // than once in a single execution (unless of course it has been re-added).
        if (isset($this->removed[ $qualifiedName ])) {
            $success = false;
        } else {
            $this->removed[ $qualifiedName ] = true;
            // Clear from memory.
            $success = $this->invalidate($qualifiedName);
        }

        return $success;
    }

    /**
     * Build a cache key for the provided information.
     *
     * @param    string $qualifiedName
     * @param    string $criteriaHash
     *
     * @return    array
     */
    protected function buildCacheKey($qualifiedName, $criteriaHash)
    {
        // If the searched model is permissible,
        // determine the capability code of the user.
        $capabilityCode = 'NULL';

        return array(
            $capabilityCode => true,
            $qualifiedName  => true,
            $criteriaHash   => false,
        );
    }

    /**
     * Returns cached output based on the provided parameters.
     *
     * If the output is not cached, <code>null</code> will be returned.
     *
     * @param    string $qualifiedName The qualified class name.
     * @param    string $criteriaHash  Hash representing search criteria.
     *
     * @return    mixed
     */
    public function retrieve($qualifiedName, $criteriaHash)
    {
        // Determine cache key.
        $key = $this->buildCacheKey($qualifiedName, $criteriaHash);

        // Check in memory.
        return $this->unserializeValue(
            $this->getFromMemory($key)
        );
    }

    /**
     * Caches publishable output.
     *
     * @param    string $qualifiedName
     * @param    string $criteriaHash
     * @param    mixed  $output           The output.
     * @param    int    $expiry           A UNIX timestamp representing the time at
     *                                    which this item will expire. If omitted, the
     *                                    cached item will never expire.
     *
     * @return    bool
     */
    public function set($qualifiedName, $criteriaHash, $output, $expiry = 0)
    {
        // Ensure valid expiry date.
        $expiry = (int)$expiry;
        // Unset the removed array entry for this class
        // and it's parents if this is being re-added.
        $parent = $qualifiedName;
        do {
            unset($this->removed[ $parent ]);
        } while ($parent = get_parent_class($parent));
        // Determine cache key.
        $key = $this->buildCacheKey($qualifiedName, $criteriaHash);
        // Compress the output for storage.
        $output = gzcompress(serialize($output));

        // Store in memory.
        return $this->storeInMemory($key, $output, false, $expiry);
    }

    /**
     * Uunserializes a cached value.
     *
     * @param    string $value Serialized value retrieved from the cache.
     *
     * @return    mixed    Unserialized value, or <code>null</code> if the value
     *                    was invalid.
     */
    protected function unserializeValue($value)
    {
        if ($value === null) {
            $unserialized = $value;
        } else {
            // Error suppression used as there is no exception based handling available.
            // As per the method documentation, null will be returned rather than
            // an error message being issued.
            $value = @gzuncompress($value);
            if ($value === false) {
                $unserialized = null;
            } else {
                $unserialized = unserialize($value);
            }
        }

        return $unserialized;
    }
}
