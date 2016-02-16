<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\cache;

use app\decibel\model\DDefinition;

/**
 * Handles the caching of objects within process and shared memory.
 *
 * @author        Timothy de Paris
 */
class DDebuggableModelCache extends DModelCache
{
    /**
     * Cache the definiton of particular object type.
     *
     * @param    DDefinition $definition
     *
     * @return    bool
     */
    public function cacheDefinition(DDefinition $definition)
    {
        // Cache the modification date of the definition.
        $qualifiedName = get_class($definition);
        $key = "mtime_{$qualifiedName}";
        $this->storeInMemory($key, self::getDefinitionMTime($qualifiedName));

        return parent::cacheDefinition($definition);
    }

    /**
     * Returns the modification time of the definition for the provided object.
     *
     * @param    string $qualifiedName Qualified name of the object.
     *
     * @return    int
     */
    protected static function getDefinitionMTime($qualifiedName)
    {
        $definitionFile = str_replace('\\', '/', DECIBEL_PATH . $qualifiedName . '.php');
        if (!file_exists($definitionFile)) {
            $definitionMtime = 0;
            $modelMtime = 0;
        } else {
            $definitionMtime = filemtime($definitionFile);
            $modelFile = str_replace('_Definition', '', $definitionFile);
            if ($definitionFile === $modelFile) {
                $modelMtime = $definitionMtime;
            } else {
                $modelMtime = filemtime($modelFile);
            }
        }
        if ($definitionMtime > $modelMtime) {
            $mtime = $definitionMtime;
        } else {
            $mtime = $modelMtime;
        }

        return $mtime;
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
        // Remove the modification date.
        $key = "mtime_{$definition}";
        $this->removeFromMemory($key);

        return parent::removeDefinition($definition);
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
        // Check the modification date of the definition before retrieving it.
        $key = "mtime_{$qualifiedName}";
        $mtime = (int)$this->getFromMemory($key);
        if (!$mtime || $mtime !== self::getDefinitionMTime($qualifiedName)) {
            $result = null;
        } else {
            $result = parent::retrieveDefinition($qualifiedName);
        }

        return $result;
    }
}
