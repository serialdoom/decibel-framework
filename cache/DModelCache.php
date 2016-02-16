<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use __PHP_Incomplete_Class;
use app\decibel\cache\debug\DQualifiedNameRequiredException;
use app\decibel\configuration\DApplicationMode;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DFullProfiler;
use app\decibel\debug\DProfiler;
use app\decibel\model\DDefinition;
use app\decibel\model\DModel;

/**
 * Handles the caching of objects within process and shared memory.
 *
 * @author        Timothy de Paris
 */
class DModelCache extends DCacheHandler
{
    /**
     * Returns the qualified name of the singleton class to be loaded.
     *
     * This allows an inheriting class to load a class other than itself (such as a child class)
     * if this is deemed appropriate for the executing scenario.
     *
     * @return    string
     */
    protected static function getSingletonClass()
    {
        if (DApplicationMode::isDebugMode()) {
            $class = DDebuggableModelCache::class;
        } else {
            $class = DModelCache::class;
        }

        return $class;
    }

    /**
     * Builds a caching key for the provided model information.
     *
     * @param    int    $id            The ID of the model being cached.
     * @param    string $qualifiedName Qualified name of the model being cached.
     *
     * @return    mixed
     * @throws    DQualifiedNameRequiredException    If a cache key cannot be generated
     *                                            for the provided parameters.
     */
    protected function buildCacheKey($id, $qualifiedName)
    {
        // Handle new model instances.
        if ($id === 0) {
            // Check that qualified name was provided.
            if ($qualifiedName === null) {
                throw new DQualifiedNameRequiredException();
            }
            // Build cache key.
            $key = "{$qualifiedName}_0";
            // Handle saved model instances.
        } else {
            // Determine qualified name for this object.
            if ($qualifiedName === null) {
                $qualifiedName = $this->getQualifiedNameForId($id);
            }
            // Build invalidator key.
            $key = array(
                $qualifiedName => true,
                $id            => true,
            );
        }

        return $key;
    }

    /**
     * Cache the definiton of particular object type.
     *
     * @param    DDefinition $definition
     *
     * @return    bool
     */
    public function cacheDefinition(DDefinition $definition)
    {
        $qualifiedName = get_class($definition);

        return $this->storeInMemory($qualifiedName, $definition);
    }

    /**
     * Retrieves the qualified name of the object with the specified ID.
     *
     * @param    int $id The ID to retrieve the qualified name for.
     *
     * @return    string    The qualified name, or null if no object exists with
     *                    the provided ID.
     * @throws    DQualifiedNameRequiredException    If an ID of 0 is provided.
     */
    public function getQualifiedNameForId($id)
    {
        // Check parameters.
        if ($id === 0) {
            throw new DQualifiedNameRequiredException();
        }
        // Build cache key.
        $key = "qualifiedName_{$id}";
        // Check in memory.
        $qualifiedName = $this->getFromMemory($key);
        if ($qualifiedName !== null) {
            return $qualifiedName;
        }
        // If not found, query the database.
        try {
            $query = new DQuery('app\\decibel\\model\\DModel-getQualifiedNameForId', array(
                'id' => $id,
            ));
            if ($query->getNumRows() > 0) {
                $qualifiedName = $query->get('qualifiedName');
                $this->storeInMemory($key, $qualifiedName);
            } else {
                $qualifiedName = null;
            }
            // Log any issue with querying the database.
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::throwException($exception);
            $qualifiedName = null;
        }

        return $qualifiedName;
    }

    /**
     * Returns a cached object.
     *
     * @param    int    $id               The ID of the object to retrieve.
     * @param    string $qualifiedName    Qualified name of the model. Required
     *                                    for new model instances (where an ID
     *                                    of 0 is provided). This parameter is
     *                                    not used for saved model instances.
     *
     * @throws    DQualifiedNameRequiredException    If no qualified name is provided
     *                                            for a new model instance.
     * @return    DModel
     */
    public function retrieve($id, $qualifiedName = null)
    {
        // Build cache key.
        $key = $this->buildCacheKey($id, $qualifiedName);
        // Check in memory.
        $modelInstance = $this->getFromMemory($key, false);
        if ($modelInstance === null
            // Check that returned value is not an incomplete class.
            // An incomplete class can be returned if a model instance in the cache
            // no longer has a corresponding class on the file system.
            || $modelInstance instanceof __PHP_Incomplete_Class
        ) {
            return null;
        }
        // Log cache action.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            $profiler = DFullProfiler::load();
            $profiler->trackObjectLoad(DFullProfiler::MODEL_LOAD_CACHE);
        }
        // Clone new objects and reset default values before returning.
        if ($modelInstance->getId() === 0) {
            $clonedObject = clone $modelInstance;
            $clonedObject->loadDefaultValues(true);
            $modelInstance = $clonedObject;
        }

        return $modelInstance;
    }

    /**
     * Retrieve the definiton of particular model type.
     *
     * @param    string $qualifiedName Qualified name of the definition to retrieve.
     *
     * @return    DDefiniton
     */
    public function retrieveDefinition($qualifiedName)
    {
        return $this->getFromMemory($qualifiedName);
    }

    /**
     * Caches a model instance.
     *
     * @param    DModel $instance The model instance to cache.
     *
     * @return    void
     */
    public function set(DModel $instance)
    {
        // Determine model information
        $qualifiedName = get_class($instance);
        $id = $instance->getId();
        // Build cache key.
        $key = $this->buildCacheKey($id, $qualifiedName);
        // Handle new model instances.
        // Store a clone of the object, otherwise we may
        // inadvertently modify the new model instance "template".
        if ($id === 0) {
            $instance = clone $instance;
        }
        // Store in memory.
        $this->storeInMemory($key, $instance, false);
    }

    /**
     * Removes the definiton of particular model type from the cache.
     *
     * @param    string $definition Qualified name of the definition to remove.
     *
     * @return    bool
     */
    public function removeDefinition($definition)
    {
        return $this->removeFromMemory($definition);
    }

    /**
     * Removes a model instance from the cache.
     *
     * @param    int    $id               The ID of the object to retrieve.
     * @param    string $qualifiedName    Qualified name of the model. Required
     *                                    for new model instances (where an ID
     *                                    of 0 is provided). This parameter is
     *                                    not used for saved model instances.
     *
     * @return    void
     * @throws    DQualifiedNameRequiredException    If no qualified name is provided
     *                                            for a new model instance.
     */
    public function removeModelInstance($id, $qualifiedName = null)
    {
        // Treat new model instances differently, as they share the ID
        // of 0 across all model types.
        if ($id === 0) {
            return $this->removeNewModelInstance($qualifiedName);
        }
        // For existing instances, just invalidate their ID.
        $this->invalidate($id);
        if (defined(DProfiler::PROFILER_ENABLED)) {
            if ($qualifiedName === null) {
                $qualifiedName = $this->getQualifiedNameForId($id);
            }
            $this->log("{$qualifiedName}_{$id}", DCacheHandler::ACTION_REMOVE);
        }
    }

    /**
     * Removes all instances of a specific model from the cache.
     *
     * @param    string $qualifiedName Qualified name of the model to remove.
     *
     * @return    void
     */
    public function removeModelInstances($qualifiedName)
    {
        $this->invalidate($qualifiedName);
    }

    /**
     * Removes a new model instance from the cache.
     *
     * @note
     * New model instances have an ID of 0.
     *
     * @param    string $qualifiedName    Qualified name of the model to remove
     *                                    a new instance for.
     *
     * @throws    DQualifiedNameRequiredException    If no qualified name is provided.
     */
    public function removeNewModelInstance($qualifiedName)
    {
        // Build cache key.
        $key = $this->buildCacheKey(0, $qualifiedName);
        // Clear the object from memory.
        $this->removeFromMemory($key);
        $this->log($key, DCacheHandler::ACTION_REMOVE);
    }
}
