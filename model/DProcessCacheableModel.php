<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\model\field\DRelationalField;

/**
 * An implementation of the {@link DProcessCacheable} interface
 * for classes extending {@link DModel}.
 *
 * @author        Timothy de Paris
 */
trait DProcessCacheableModel
{
    /**
     * Caches loaded objects and definitions.
     *
     * This is used by the {@link DModel::create()} function to optimise
     * performance when models are accessed multiple times within a single
     * execution. The {@link app::decibel::cache::DModelCache DModelCache}
     * is reverted to for loading each model the first time within an execution.
     *
     * @var        array
     */
    public static $cache = array();
    /**
     * Keeps track of the number of times a model instance has been loaded.
     *
     * Currently the best method available for garbage collection, this will
     * make sure a model instance can't be freed accidentally while being
     * edited.
     *
     * @var        array
     */
    public static $cacheCount = array();
    /**
     * Used to track which objects have been uncached within a process, to avoid
     * an indefinite loop where two objects reference each other.
     *
     * @var        array
     */
    protected static $uncached = array();

    /**
     * Caches a model instance in process memory.
     *
     * @param    DModel $instance
     *
     * @return    void
     */
    protected static function cacheInProcess(DModel $instance)
    {
        $id = $instance->getId();
        if ($id !== 0) {
            self::$cache[ $id ] = $instance;
            self::$cacheCount[ $id ] = 1;
        }
    }

    /**
     * Clears the cached reference to this model allowing associated
     * memory to be freed.
     *
     * @return    void
     */
    public function free()
    {
        // Free the collected IDs.
        $toFree = $this->getIdsToFree();
        foreach ($toFree as $id) {
            if (isset(self::$cache[ $id ])) {
                $this->freeInstance($id);
            }
        }
    }

    /**
     * Free a model instance from the process memory cache.
     *
     * @param    int $id
     *
     * @return    void
     */
    protected function freeInstance($id)
    {
        // Never free an object while it has unsaved changes!
        if (!self::$cache[ $id ]->hasUnsavedChanges()) {
            // Reduce the cache count.
            --self::$cacheCount[ $id ];
            // Unset the cache if the cache count has reached zero
            // (i.e. there are no more references to it in use).
            if (self::$cacheCount[ $id ] === 0) {
                unset(self::$cache[ $id ]);
            }
        }
    }

    /**
     * Returns an array containing the IDs of each model instance that this
     * model instance holds a link to.
     *
     * @return    array
     */
    protected function getIdsToFree()
    {
        // Free all models stored as field pointers.
        $toFree = array();
        $queue = array($this);
        while ($queue) {
            /* @var $next DModel */
            $next = array_pop($queue);
            $toFree[] = $next->getId();
            $relationalFields = $next->getFieldsOfType(DRelationalField::class);
            foreach ($relationalFields as $field) {
                /* @var $field DRelationalField */
                $this->getIdsToFreeForField($next, $field, $queue, $toFree);
            }
        }

        return $toFree;
    }

    /**
     *
     * @param    DModel           $instance
     * @param    DRelationalField $field
     * @param    array            $queue
     * @param    array            $toFree
     *
     * @return    void
     */
    protected function getIdsToFreeForField(DModel $instance,
                                            DRelationalField $field, array &$queue, array &$toFree)
    {
        $fieldName = $field->getName();
        $values = $instance->getFieldValue($fieldName);
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($values as $value) {
            if (is_object($value)
                && !in_array($value->getId(), $toFree)
            ) {
                $queue[] = $value;
            }
        }
    }

    /**
     * Retrieves a model instance from the process cache, if available.
     *
     * @param    int $id
     *
     * @return    DModel    The cached instance, or <code>null</code> if the instance
     *                    with the provided ID is not in the cache.
     */
    protected static function retrieveFromProcessCache($id)
    {
        // Check locally cached models.
        if ($id != 0
            && isset(self::$cache[ $id ])
        ) {
            ++self::$cacheCount[ $id ];
            $instance = self::$cache[ $id ];
        } else {
            $instance = null;
        }

        return $instance;
    }
}
