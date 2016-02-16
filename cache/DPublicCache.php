<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\cache\DCacheHandler;
use app\decibel\cache\debug\DKeyTooLongException;

/**
 * Provides public caching of information.
 *
 * This can be utilised by Apps to cache any generated information while
 * still obeying the application caching configuration.
 *
 * @author    Timothy de Paris
 */
class DPublicCache extends DCacheHandler
{
    /**
     * Used to track keys that have been removed, to avoid trying this
     * twice in a single page load.
     *
     * @var        array
     */
    protected $removed = array();

    /**
     * Removes information from the cache.
     *
     * @param    string $invalidatorKey   The cache invalidator key.
     * @param    int    $id               The id of the information.
     *                                    If not provided, the entire cache segment
     *                                    specified by the invalidator ID will
     *                                    be cleared.
     *
     * @return    bool    <code>true</code> if the value was removed from the
     *                    cache, <code>false</code> if not or <code>null</code>
     *                    if the action has been buffered.
     */
    public function remove($invalidatorKey, $id = null)
    {
        // Clear a cache segment.
        if ($id === null) {
            $result = $this->invalidate($invalidatorKey);
            // Or clear a specific entry.
        } else {
            $invalidatorId = $this->getInvalidationId($invalidatorKey);
            $key = "{$invalidatorId}_{$id}";
            // Track what's being removed so that we can avoid removing it more
            // than once in a single execution (unless of course it has been re-added).
            if (isset($this->removed[ $key ])) {
                $result = null;
            } else {
                $this->removed[ $key ] = true;
                // Determine ids required to remove from memory.
                $result = $this->removeFromMemory($key);
            }
        }

        return $result;
    }

    /**
     * Returns a pointer to cached information. If the information is not
     * cached, null will be returned.
     *
     * @param    string $invalidatorKey   The cache invalidator key. This is used
     *                                    to clear a segment of the public cache
     *                                    rather than individual entries.
     * @param    string $id               The id of the information.
     *
     * @return    mixed
     */
    public function retrieve($invalidatorKey, $id)
    {
        // Build key.
        $invalidatorId = $this->getInvalidationId($invalidatorKey);
        $key = "{$invalidatorId}_{$id}";

        // Check in memory.
        return $this->getFromMemory($key);
    }

    /**
     * Prepares a value to be cached.
     *
     * @param    mixed $value Value being cached.
     *
     * @return    mixed
     */
    protected function serializeValue($value)
    {
        // If information is an object, clone the object before serialising or
        // all pointers to the object will point to the serialised version.
        if (is_object($value)) {
            $serialized = clone $value;
        } else {
            $serialized =& $value;
        }

        return $serialized;
    }

    /**
     * Inserts data into the cache.
     *
     * @param    string $invalidatorKey   The cache invalidator key. This is used
     *                                    to clear a segment of the public cache
     *                                    rather than individual entries.
     * @param    int    $id               The id of the information.
     * @param    mixed  $value            Pointer to the information to cache.
     * @param    int    $expiry           A UNIX timestamp representing the time at
     *                                    which this item will expire. If omitted, the
     *                                    cached item will never expire.
     *
     * @return    bool    <code>true</code> if the value was set into the cache,
     *                    <code>false</code> if not.
     * @throws    DKeyTooLongException    If the key exceeds that maximum allowed length.
     */
    public function set($invalidatorKey, $id, $value, $expiry = 0)
    {
        // Check key length.
        if (strlen($id) > 200) {
            throw new DKeyTooLongException($id, 200);
        }
        // Build key.
        $invalidatorId = $this->getInvalidationId($invalidatorKey);
        $key = "{$invalidatorId}_{$id}";
        // Unset any existing 'removed' entry if this is being re-added.
        unset($this->removed[ $key ]);

        // Store in cache.
        return $this->storeInMemory(
            $key,
            $this->serializeValue($value),
            true,
            // Ensure valid expiry date.
            (int)$expiry
        );
    }
}
