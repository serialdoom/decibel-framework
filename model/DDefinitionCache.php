<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\cache\DModelCache;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\debug\DException;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\registry\DClassQuery;
use app\decibel\registry\DInvalidClassNameException;
use ReflectionClass;

/**
 * Provides caching functionality for definitions.
 *
 * @author    Timothy de Paris
 */
trait DDefinitionCache
{
    /**
     * Map containing known definitions.
     *
     * @var        array
     */
    protected static $definitions = array();

    /**
     * Determines the type of {@link DDefinition} required by the
     * specified {@link DDefinable} class.
     *
     * @param    string $definable    Qualified name of the {@link DDefinable}
     *                                to retrieve a definition for.
     *
     * @return    string
     * @throws    DInvalidClassNameException    If the {@link DDefinable} requests
     *                                        a definition class that does not exist.
     */
    protected static function getDefinitionClass($definable)
    {
        // Determine the qualified name of the definition to load.
        $definition = $definable::getDefinitionName();
        if (!class_exists($definition)) {
            throw new DInvalidClassNameException($definition);
        }

        return $definition;
    }

    /**
     * Loads the definition for the specified definable object and caches it
     * in process memory and the {@link DModelCache}.
     *
     * @param    string $definable    Qualified name of the {@link DDefinable}
     *                                to retrieve a definition for.
     * @param    string $definition   Qualified name of the {@link DDefinition} to load.
     *
     * @throws    DException    If the definition fails to load.
     */
    protected static function loadFromDisk($definable, $definition)
    {
        // Find and load the definition.
        try {
            $reflectionClass = new ReflectionClass($definition);
            if ($reflectionClass->isAbstract()) {
                return null;
            }
            self::$definitions[ $definable ] = new $definition($definable);
            // If an exception is triggered, append the name of the
            // definition it occured in.
        } catch (DException $exception) {
            $exception->appendToMessage(" in <code>{$definition}</code>");
            throw $exception;
        }
        // Cache the definition in memory.
        $modelCache = DModelCache::load();
        $modelCache->cacheDefinition(self::$definitions[ $definable ]);
        // Check for table definition modifications
        $currentTableDefinition = DTableDefinition::createFromTable(DDatabaseMapper::getTableNameFor($definable));
        $modelTableDefinition = self::$definitions[ $definable ]->getTableDefinition();
        // Update existing table
        if ($currentTableDefinition) {
            $currentTableDefinition->mergeWith($modelTableDefinition);
            // Create new table
        } else {
            $modelTableDefinition->createTable();
        }
    }

    /**
     * Removes a definition from process memory and the {@link DModelCache}.
     *
     * @param    string $definable            Qualified name of the {@link DDefinable} class to
     *                                remove the definition for.
     * @param    bool   $removeInheritees     Whether to also remove definitions
     *                                        that extend from this definition.
     *
     * @return    void
     */
    public static function remove($definable, $removeInheritees = false)
    {
        // Determine all definables to remove.
        $definables = array($definable);
        if ($removeInheritees) {
            $inheritees = DClassQuery::load()
                                     ->setAncestor($definable)
                                     ->getClassNames();
            $definables = array_merge($definables, $inheritees);
        }
        // Remove from the model cache.
        $modelCache = DModelCache::load();
        foreach ($definables as $definable) {
            $modelCache->removeDefinition($definable::getDefinitionName());
            // Remove any cached models that use this definition.
            $modelCache->removeModelInstances($definable);
            // Remove from process memory.
            unset(self::$definitions[ $definable ]);
        }
    }

    /**
     * Attempts to retrieve a {@link DDefinition} from the cache.
     *
     * @param    string $definable    Qualified name of the {@link DDefinable}
     *                                to retrieve a definition for.
     *
     * @return    DDefinition    The cached definition, or <code>null</code> if the definition
     *                        is not available.
     * @throws    DInvalidClassNameException    If the {@link DDefinable} requests
     *                                        a definition class that does not exist.
     */
    public static function retrieve($definable)
    {
        // Definition has previously been loaded.
        if (isset(self::$definitions[ $definable ])) {
            $definition = self::$definitions[ $definable ];
        } else {
            $definitionClass = static::getDefinitionClass($definable);
            // Check for the definition in the memory cache.
            if (static::retrieveFromMemory($definable, $definitionClass)) {
                $definition = self::$definitions[ $definable ];
            } else {
                static::loadFromDisk($definable, $definitionClass);
                $definition = self::$definitions[ $definable ];
            }
        }

        return $definition;
    }

    /**
     * Attempts to retrieve a {@link DDefinition} from the {@link DModelCache}.
     *
     * @param    string $definable    Qualified name of the {@link DDefinable}
     *                                to retrieve a definition for.
     * @param    string $definition   Qualified name of the {@link DDefinition} to load.
     *
     * @return    bool    <code>true</code> if the definition was retrieved,
     *                    <code>false</code> if it was not found in memory.
     */
    protected static function retrieveFromMemory($definable, $definition)
    {
        $modelCache = DModelCache::load();
        self::$definitions[ $definable ] = $modelCache->retrieveDefinition($definition);

        return (self::$definitions[ $definable ] !== null);
    }
}
