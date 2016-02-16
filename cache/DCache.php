<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\cache\debug\DCacheException;
use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DFullProfiler;
use app\decibel\debug\DProfiler;
use app\decibel\service\DServiceContainer;

/**
 * Base class for all available types of caching.
 *
 * @author        Timothy de Paris
 */
abstract class DCache extends DServiceContainer implements DAdaptable
{
    use DAdapterCache;

    /**
     * Unique caching ID for this Decibel installation.
     *
     * This ID is used to avoid conflict between different installations of
     * decibel on a single machine, where a shared caching mechanism is used.
     *
     * The ID is generated based on the location of the decibel installation.
     *
     * @var        string
     */
    protected $installId;

    /**
     * Invalidation ID for this Decibel installation.
     *
     * This ID is mapped to the install ID within the memory cache, allowing
     * the entire content to be invalidated with one cache action.
     *
     * The ID is generated based on the location of the decibel installation
     * and the timestamp at generation.
     *
     * @var        string
     */
    private $invalidationId;

    /**
     * Stores invalidation IDs for the request after their first access.
     *
     * @var        array
     */
    public $invalidationIdCache = array();

    /**
     * Initialises the cache.
     *
     * @param    bool $initialise     Whether to initialise the cache. Passing
     *                                false can allow the cache to be loaded
     *                                to retrieve configuration information,
     *
     * @return    static
     * @throws    DCacheException    If the cache is unable to be initialised.
     */
    final public function __construct($initialise = true)
    {
        // Determine invalidation ID.
        // In production mode, include the timestamp of the registry
        // as this will force a cache clear if the registry changes.
        $this->installId = md5(__FILE__);
        if (DApplicationMode::isProductionMode()) {
            $this->installId .= filemtime(DECIBEL_PATH . 'app/app.registry');
        }
        // Initialise the cache.
        if ($initialise) {
            $this->initialise();
            $this->invalidationId = $this->getInvalidationId($this->installId);
        }
    }

    /**
     * Returns an invalidation ID for the specified key.
     *
     * If required, the ID will be generated and stored in the memory cache.
     *
     * @param   string $key       Caching key.
     * @param    bool  $force     If true, generation of the invalidation ID will
     *                            occur even if one already exists.
     *
     * @return    string
     */
    final public function getInvalidationId($key, $force = false)
    {
        // If this is not the install ID, prefix it with the invalidation ID.
        if ($key !== $this->installId) {
            $key = $this->installId . '_' . $key;
        }
        // If we aren't forcing regeneration, try to load the invalidation ID
        // from the process cache or the memory cache.
        if (!$force) {
            $invalidationId = $this->loadInvalidationId($key);
        } else {
            $invalidationId = null;
            // Track invalidation action (i.e. force parameter set to true)
            if (defined(DProfiler::PROFILER_ENABLED)) {
                DFullProfiler::load()->trackMemoryCacheAction(DFullProfiler::MEMORY_CACHE_INVALIDATION);
            }
        }
        // Generate an ID and store it in the cache.
        if ($invalidationId === null) {
            $invalidationId = md5($key . microtime(true));
            $this->setValue($key, $invalidationId);
            $this->invalidationIdCache[ $key ] = $invalidationId;
            // Track cache action.
            if (defined(DProfiler::PROFILER_ENABLED)) {
                DFullProfiler::load()->trackMemoryCacheAction(DFullProfiler::MEMORY_CACHE_ADD);
            }
        }

        return $invalidationId;
    }

    /**
     * Attempts to load the invalidation ID for the specified key
     * from process memory or the shared memory cache.
     *
     * @param   string $key Caching key.
     *
     * @return    string    The invalidation ID,
     *                    or <code>null</code> if none exists.
     */
    protected function loadInvalidationId($key)
    {
        if (isset($this->invalidationIdCache[ $key ])) {
            $invalidationId = $this->invalidationIdCache[ $key ];
        } else {
            $invalidationId = $this->getValue($key);
            $this->invalidationIdCache[ $key ] = $invalidationId;
            // Track cache action.
            if (defined(DProfiler::PROFILER_ENABLED)) {
                if ($invalidationId === null) {
                    $action = DFullProfiler::MEMORY_CACHE_MISS;
                } else {
                    $action = DFullProfiler::MEMORY_CACHE_HIT;
                }
                DFullProfiler::load()->trackMemoryCacheAction($action);
            }
        }

        return $invalidationId;
    }

    /**
     * Initialises any required functionality for the cache.
     *
     * @return    void
     * @throws    DCacheInitialisationException    If the cache cannot be initialised.
     */
    abstract protected function initialise();

    /**
     * Returns a singleton instance of the cache class that has been selected
     * for this installation in the {@link app::decibel::application::DConfigurationManager DConfigurationManager}.
     *
     * @return    DCache
     */
    final public static function load()
    {
        // If called on this class (DCache), return the cache selected
        // in DConfigurationManager.
        $class = get_called_class();
        if ($class === self::class) {
            $configuration = DCacheConfiguration::load();
            $class = $configuration->getCache();
        }
        if (!isset(self::$instances[ $class ])) {
            // If anything goes wrong, load the Null cache instead.
            try {
                self::$instances[ $class ] = new $class();
            } catch (DCacheException $e) {
                DErrorHandler::throwException($e);
                self::$instances[ $class ] = new DNullCache();
            }
        }

        return self::$instances[ $class ];
    }

    /**
     * Determines if this cache wrapper can support cache clustering.
     *
     * If the application is running in clustered mode, the selected cache type
     * should also support clustering.
     *
     * @return    bool
     */
    abstract public function supportsClustering();

    /**
     * Retrieves information from the cache.
     *
     * @param    string $key The key of the information to retrieve.
     *
     * @return    mixed    The requested information, or <code>null</code>
     *                    if no value was found.
     */
    abstract protected function getValue($key);

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
     *                                    allowed length.
     */
    abstract protected function setValue($key, $value, $expiry = 0);

    /**
     * Removes information from the cache.
     *
     * @param    string $key The key of the information to remove.
     *
     * @return    bool    <code>true</code> if the information was successfully
     *                    removed, <code>false</code> otherwise.
     */
    abstract protected function removeValue($key);

    /**
     * Clears all information from the cache for this Decibel installation.
     *
     * This is achieved by removing the invalidation ID key from the cache,
     * therefore requiring a new installation ID to be generated. Cached
     * information will not be immediately removed from the cache using this
     * method, however will be forced out by newer entries over time.
     *
     * @return    void
     */
    final public function clear()
    {
        $this->invalidationId = $this->getInvalidationId($this->installId, true);
    }

    /**
     * Retrieves data from the cache.
     *
     * @param    string $key A unique key for the data.
     *
     * @return    mixed
     */
    final public function get($key)
    {
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $executionTime = microtime(true);
        }
        // Store in shared cache.
        $cacheKey = $this->invalidationId . '_' . $key;
        $value = $this->getValue($cacheKey);
        // Track cache action.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            DFullProfiler::load()->trackMemoryCacheAction(
                ($value === null) ? DFullProfiler::MEMORY_CACHE_MISS : DFullProfiler::MEMORY_CACHE_HIT,
                microtime(true) - $executionTime
            );
        }

        return $value;
    }

    /**
     * Stores information in the cache.
     *
     * @param    string $key              The key to store the information against.
     * @param    mixed  $value            The value to store. The value will be
     *                                    serialized to ensure consistency across
     *                                    different cache implementations.
     * @param    int    $expiry           The timestamp at which the cached information
     *                                    will expire. If omitted or zero, the information
     *                                    will be cached indefinitely.
     *
     * @return    bool    true if the information was stored successfully,
     *                    otherwise false.
     * @throws    DKeyTooLongException    If the provided key exceeds the maximum
     *                                    allowed length.
     */
    final public function set($key, $value, $expiry = 0)
    {
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $executionTime = microtime(true);
        }
        // For objects, cache a clone so that object data is not serialised.
        if (is_object($value)) {
            $value = clone($value);
        }
        // Store in shared cache.
        $cacheKey = $this->invalidationId . '_' . $key;
        $result = $this->setValue($cacheKey, $value, $expiry);
        // Track cache action.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            DFullProfiler::load()->trackMemoryCacheAction(
                DFullProfiler::MEMORY_CACHE_ADD,
                microtime(true) - $executionTime
            );
        }

        return $result;
    }

    /**
     * Removes a value from the cache.
     *
     * @param    string $key Key of the value to remove.
     *
     * @return    bool    Whether the item was successfully removed.
     */
    final public function remove($key)
    {
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $executionTime = microtime(true);
        }
        // Remove from shared memory.
        $cacheKey = $this->invalidationId . '_' . $key;
        $result = $this->removeValue($cacheKey);
        // Track cache action.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            DFullProfiler::load()->trackMemoryCacheAction(
                DFullProfiler::MEMORY_CACHE_REMOVE,
                microtime(true) - $executionTime
            );
        }

        return $result;
    }
}
