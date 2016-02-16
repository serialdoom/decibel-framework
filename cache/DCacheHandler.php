<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\cache;

use app\decibel\application\DConfigurationManager;
use app\decibel\cache\DCache;
use app\decibel\cache\debug\DSerializationException;
use app\decibel\debug\DProfiler;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DResult;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Base class for Cache Handlers.
 *
 * @author        Timothy de Paris
 */
abstract class DCacheHandler implements DSingleton
{
    use DSingletonClass;

    /**
     * Information found in memory.
     *
     * @var        string
     */
    const ACTION_HIT = 'Hit';

    /**
     * Information not found in the cache.
     *
     * @var        string
     */
    const ACTION_MISS = 'Miss';

    /**
     * Information removed from the cache.
     *
     * @var        string
     */
    const ACTION_REMOVE = 'Removed';

    /**
     * Information added to the cache.
     *
     * @var        string
     */
    const ACTION_ADD = 'Added';

    /**
     * 'Group' constant name.
     *
     * @var        string
     */
    const CONST_GROUP = 'GROUP';

    /**
     * Unique caching ID for this cache handler.
     *
     * This ID is pre-pended to all cache records for this handler and can
     * be used to invalidate the entire cache handler.
     *
     * @var        string
     */
    private $invalidationId;

    /**
     * Determines whether the cache should attempt to clear process data
     * in order to free memory.
     *
     * Decibel will track if clear attempts succesfully reduce the amount
     * of memory available, and stop further clear attempts if this is not
     * the case to avoid infinite loops.
     *
     * @var        bool
     */
    private $attemptClear = true;

    /**
     * Garbage collector counter.
     *
     * @var        int
     */
    private $gcCount = 0;

    /**
     * The internal memory limit for the application, as defined
     * by the {@link DConfigurationManager}
     *
     * This is stored here by the constructor to optimise performace.
     *
     * @var        float
     */
    private $internalMemoryLimit;

    /**
     * Whether buffering has been enabled.
     *
     * @var        int
     */
    private static $buffering = 0;

    /**
     * Used to buffer invalidation requests.
     *
     * @var        array
     */
    private $invalidationBuffer = array();

    /**
     * Used to buffer removal requests for the shared memory cache when
     * buffering is enabled.
     *
     * @var        array
     */
    private $memoryBuffer = array();

    /**
     * Stores information to be written to the cache on page load completion.
     *
     * @var        array
     */
    private $buffer;

    /**
     * Stores information about the fields to be written to the cache
     * on page load completion.
     *
     * @var        array
     */
    private $bufferKeys;

    /**
     * Logs the activity of caches.
     *
     * @var array
     */
    private $log = [];

    /**
     * Shared instance of the Memory Cache handler.
     *
     * @var        DCache
     */
    protected static $memoryCache;

    /**
     * Stores cached data in process memory.
     *
     * This allows information to be recalled directly from process memory
     * where a particular value may need to be used multiple times in a single
     * page execution.
     *
     * @var        array
     */
    private static $processCache = array();

    /**
     * Shared instance of the profiler.
     *
     * @var        DProfiler
     */
    protected static $profiler;

    /**
     * Creates a new CacheHandler object.
     *
     * @return    void
     */
    protected function __construct()
    {
        // Store a reference to the cache and profiler.
        // Only the first time this class is loaded though!
        if (!self::$memoryCache) {
            self::$memoryCache = DCache::load();
            self::$profiler = DProfiler::load();
        }
        $this->log = array();
        $this->buffer = array();
        $this->bufferKeys = array();
        // Get an invalidation ID based on the handler's qualified name,
        // this will allow us to flush the entire handler.
        $this->invalidationId = $this->getInvalidationId(get_class($this));
        // Cache the internal memory limit.
        $configurationManager = DConfigurationManager::load();
        $this->internalMemoryLimit = $configurationManager->getInternalMemoryLimit();
    }

    /**
     * Checks the process memory limit and attempts to clear some memory if
     * PHP is close to running out of memory.
     *
     * @param    bool $clearProcess       If set to true, the process cache
     *                                    will be cleared when memory is low.
     *
     * @return    void
     */
    protected function checkMemoryLimit($clearProcess = true)
    {
        ++$this->gcCount;
        if ($this->gcCount % 10 !== 0) {
            return;
        }
        // If we are approaching the memory limit, clear the
        // process' memory cache and flush all caches.
        if ($this->attemptClear
            && $this->internalMemoryLimit
            && memory_get_usage() >= $this->internalMemoryLimit
        ) {
            // Clear the process cache if requested.
            if ($clearProcess) {
                self::$processCache = array();
            }
            self::flush();
            gc_collect_cycles();
            // Determine if flush was successful.
            if (memory_get_usage() < $this->internalMemoryLimit) {
                $this->attemptClear = false;
            }
        }
    }

    /**
     * Clears all information from the cache.
     *
     * @return null
     */
    public function clear()
    {
        // Clear process cache.
        self::$processCache = array();
        // Invalidate the handler.
        $this->invalidate(get_class($this));
        return null;
    }

    /**
     * Retrieves data from the memory cache.
     *
     * @param    string $key              A unique key for the data.
     * @param    bool   $storeInProcess   Whether data retrieved from shared
     *                                    memory will be stored in process memory.
     * @param    bool   $fromMemory       If the data was found in process memory,
     *                                    this pointer will be set to true,
     *                                    otherwise it will be set to false.
     *
     * @return    mixed
     */
    public function &getFromMemory($key, $storeInProcess = true,
                                   &$fromMemory = null)
    {
        // Flatten the key.
        $cacheKey = $this->flattenKey($key);
        // Look for the information in this process memory.
        if (isset(self::$processCache[ $this->invalidationId ][ $cacheKey ])) {
            $fromMemory = true;
            $value =& self::$processCache[ $this->invalidationId ][ $cacheKey ];
        } else {
            $value = self::$memoryCache->get("{$this->invalidationId}_{$cacheKey}");
        }
        if ($value === null) {
            $this->log($key, self::ACTION_MISS);
        } else {
            $this->log($key, self::ACTION_HIT);
            // Store in process memory after checking memory limit.
            if ($storeInProcess
                && !$fromMemory
            ) {
                $this->checkMemoryLimit();
                self::$processCache[ $this->invalidationId ][ $cacheKey ] =& $value;
            }
        }

        return $value;
    }

    /**
     * Returns an invalidation ID for the specified key.
     *
     * If required, the ID will be generated and stored in the memory cache.
     *
     * @param   string $key Project istallation Id
     *
     * @return    string
     */
    final protected function getInvalidationId($key)
    {
        return self::$memoryCache->getInvalidationId($key);
    }

    /**
     * Returns the cache log.
     *
     * @return    array
     */
    public function &getLog()
    {
        return $this->log;
    }

    /**
     * Invalidates a key from the memory cache.
     *
     * @param    string $key The key to invalidate.
     *
     * @return    bool    Result of the invalidation, or <code>null</code>
     *                    if the invalidation has been buffered.
     */
    final protected function invalidate($key)
    {
        // Buffer the invalidation request if required.
        if (self::$buffering > 0) {
            if (in_array($key, $this->invalidationBuffer)) {
                $success = null;
            } else {
                $this->invalidationBuffer[] = $key;
                $success = null;
            }
        } else {
            // Invalidate by forcing generation of a new invalidation ID.
            self::$memoryCache->getInvalidationId($key, true);
            $success = true;
        }

        return $success;
    }

    /**
     * Logs a cache action.
     *
     * @param    mixed  $key          ID of the cached information.
     *                                This can in string or array format.
     * @param    string $action       The action being logged.
     *
     * @return    void
     */
    protected function log($key, $action)
    {
        if (!defined(DProfiler::PROFILER_ENABLED)) {
            return;
        }
        if (is_array($key)) {
            $key = implode('_', array_keys($key));
        }
        if (!isset($this->log[ $key ])) {
            $this->log[ $key ] = array(
                self::ACTION_HIT    => 0,
                self::ACTION_MISS   => 0,
                self::ACTION_REMOVE => 0,
                self::ACTION_ADD    => 0,
            );
        }
        ++$this->log[ $key ][ $action ];
        // Track generic cache activity in the Application Profiler.
        self::$profiler->trackCacheAction($action);
    }

    /**
     * Converts an array style invalidator key to its string equivalent.
     *
     * @param    array $key The key to convert.
     *
     * @return    string
     */
    protected function flattenKey($key)
    {
        // Process the key.
        if (is_array($key)) {
            foreach ($key as $part => $invalidator) {
                if ($invalidator) {
                    $key[ $part ] = $this->getInvalidationId($part);
                } else {
                    $key[ $part ] = $part;
                }
            }
            $key = implode('_', array_values($key));
        }

        return $key;
    }

    /**
     * Clears information matching the provided parameters from the memory cache.
     *
     * @param    string $key The key to remove.
     *
     * @return    bool    Whether the removal was successful, or <code>null</code>
     *                    if buffering was enabled.
     */
    protected function removeFromMemory($key)
    {
        // Store for later if buffering is enabled.
        if (self::$buffering) {
            if (!isset($this->memoryBuffer[ $this->invalidationId ])
                || !in_array($key, $this->memoryBuffer[ $this->invalidationId ])
            ) {
                $this->memoryBuffer[ $this->invalidationId ][] = $key;
            }

            return null;
        }
        // Remove from process memory.
        unset(self::$processCache[ $this->invalidationId ][ $key ]);
        // Remove from memory cache.
        $result = self::$memoryCache->remove("{$this->invalidationId}_{$key}");
        $this->log($key, self::ACTION_REMOVE);

        return $result;
    }

    /**
     *
     * @param    string $key              The key to store the data with.
     * @param    mixed  $value            The value to store in the cache.
     * @param    bool   $storeInProcess   Whether to store the value in process
     *                                    memory.
     * @param    int    $expiry           Timestamp at which the data will expire.
     *
     * @return    bool
     */
    protected function storeInMemory($key, $value, $storeInProcess = true, $expiry = 0)
    {
        // Process the key.
        $cacheKey = $this->flattenKey($key);
        $result = self::$memoryCache->set(
            "{$this->invalidationId}_{$cacheKey}",
            $value,
            $expiry
        );
        // Store in process memory if required.
        if ((!$result || $storeInProcess)
            && $expiry === 0
        ) {
            $this->checkMemoryLimit();
            self::$processCache[ $this->invalidationId ][ $cacheKey ] = $value;
        }
        $this->log($key, self::ACTION_ADD);

        return $result;
    }

    /**
     * Clears cached information.
     *
     * @param    array $handlers Qualified names of the {@link DCacheHandler} classes to clear.
     *                                If not specified, all cache handlers will be cleared.
     *
     * @return    DResult
     */
    public static function clearCaches(array $handlers = null)
    {
        $result = new DResult('Cache', 'cleared');
        $allHandlers = DClassQuery::load()
                                  ->setAncestor(self::class)
                                  ->getClassNames();
        if ($handlers === null) {
            $handlers = $allHandlers;
            // If handlers have been provided, ensure they are all valid.
        } else {
            $handlers = array_intersect($handlers, $allHandlers);
        }
        // Clear each of the requested caches.
        foreach ($handlers as $handler) {
            $handler::load()->clear();
            $result->addMessage("{$handler} cleared.");
        }

        return $result;
    }

    /**
     * Clears all information from the cache related to the specified
     * capability codes.
     *
     * @param    array $capabilityCodes The capability codes to clear.
     *
     * @return    void
     */
    public static function clearCapabilitiesCaches($capabilityCodes)
    {
        $caches = DClassQuery::load()
                             ->setAncestor('app\\decibel\\cache\\DCapabilityAware')
                             ->getClassNames();
        foreach ($caches as $cache) {
            $cache::load()->clearCapabilities($capabilityCodes);
        }
    }

    /**
     * Flushes all buffered cache actions.
     *
     * This is called by the {@link DCacheHandler::stopBuffering()} function,
     * and is also called as a shutdown function in case buffering
     * is not stopped.
     *
     * @return    void
     */
    public static function flush()
    {
        $cacheHandlers = DClassQuery::load()
                                    ->setAncestor(self::class)
                                    ->getClassNames();
        foreach ($cacheHandlers as $qualifiedName) {
            // If this cache hasn't been loaded during the execution
            // of this script, there can't be anything to flush,
            // so don't bother proceeding in this case.
            if (!class_exists($qualifiedName, false)) {
                continue;
            }
            // Load the cache handler.
            $cacheHandler = $qualifiedName::load();
            // Flush invalidation requests.
            foreach ($cacheHandler->invalidationBuffer as $key) {
                self::$memoryCache->getInvalidationId($key, true);
            }
            // Clear the buffer.
            $cacheHandler->invalidationBuffer = array();
            // Flush memory cache clear requests.
            foreach ($cacheHandler->memoryBuffer as $invalidationId => $keys) {
                foreach ($keys as $key) {
                    // Remove from process memory.
                    unset(self::$processCache[ $invalidationId ][ $key ]);
                    // Remove from memory cache.
                    self::$memoryCache->remove("{$invalidationId}_{$key}");
                    $cacheHandler->log($key, self::ACTION_REMOVE);
                }
            }
            // Clear the buffer.
            $cacheHandler->memoryBuffer = array();
        }
    }

    /**
     * Starts buffering of the cache.
     *
     * All calls to add and remove content from the cache will be stored
     * in process memory until buffering is stopped.
     *
     * @return    void
     */
    public static function startBuffering()
    {
        ++self::$buffering;
    }

    /**
     * Ends buffering of the cache.
     *
     * As buffering calls can be nested, the buffer will only be flushed
     * once the outer most buffer is stopped.
     *
     * @return    int        The number of remaining buffer levels.
     */
    public static function stopBuffering()
    {
        if (self::$buffering === 0) {
            return 0;
        }
        --self::$buffering;
        if (self::$buffering === 0) {
            self::flush();
        }

        return self::$buffering;
    }

    /**
     * Unserializes the provided data.
     *
     * @param    string $data The serialized data.
     *
     * @return    mixed
     * @throws    DSerializationException    If the serialized data was invalid.
     */
    public static function unserialize($data)
    {
        $result = @unserialize($data);
        if ($result === false
            && $data !== 'b:0;'
        ) {
            throw new DSerializationException($data, get_called_class());
        }

        return $result;
    }
}
