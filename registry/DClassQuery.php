<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\cache\DPublicCache;
use app\decibel\configuration\DApplicationMode;

/**
 * Provides a fluent interface to allow querying of class information held
 * by the {@link DClassInformation} registry hive.
 *
 * @note
 * This class also provides caching of returned results, meaning it should
 * be favoured over direct access to the {@link DClassInformation} registry
 * hive for performance reasons.
 *
 * @author    Timothy de Paris
 */
class DClassQuery
{
    /**
     * Filter bit for class information retrieval functions to only return
     * abstract classes.
     *
     * @var        int
     */
    const FILTER_ABSTRACT = 2;

    /**
     * Filter bit for class information retrieval functions to only return
     * concrete (non-abstract) classes.
     *
     * @var        int
     */
    const FILTER_CONCRETE = 1;

    /**
     * Filter bit for class information retrieval functions to only return
     * leaf classes (that is, they are not extended by any other class).
     *
     * @var        int
     */
    const FILTER_LEAF = 4;

    /**
     * If provided, only classes extending this class will be returned.
     *
     * @var        string
     */
    protected $ancestor;

    /**
     * The class information to query.
     *
     * @var        DClassInformation
     */
    protected $classInformation;

    /**
     * Bitwise filter to apply to the query.
     *
     * @var        int
     */
    protected $filter = self::FILTER_CONCRETE;

    /**
     * List of abstract classes, cached here after first used
     * by {@link DClassQuery::isAbstract()}
     *
     * @var        array
     */
    private static $abstractClasses;

    /**
     * Creates a new {@link DClassQuery} object.
     *
     * @param    DClassInformation $classInformation      The class information to
     *                                                    query. If not provided,
     *                                                    class information will
     *                                                    be retrieved from the
     *                                                    global registry.
     *
     * @return    static
     */
    public function __construct(DClassInformation $classInformation = null)
    {
        $this->classInformation = $classInformation;
    }

    /**
     * Creates a new {@link DClassQuery} object.
     *
     * @param    DClassInformation $classInformation      The class information to
     *                                                    query. If not provided,
     *                                                    class information will
     *                                                    be retrieved from the
     *                                                    global registry.
     *
     * @return    static
     */
    public static function load(DClassInformation $classInformation = null)
    {
        return new static($classInformation);
    }

    /**
     * Returns the cache key that should be used to store and retrieve
     * the results of this query.
     *
     * @param    string $method   Name of the method requesting the cache key.
     *                            This is used to ensure different results styles
     *                            are cached under unique keys.
     *
     * @return    string
     */
    protected function getCacheKey($method)
    {
        if ($this->classInformation === null) {
            $registry = 'global';
        } else {
            $classInformation = $this->getClassInformation();
            $registry = $classInformation
                ->getRegistry()
                ->getRelativePath();
        }

        return implode('_', array(
            $registry,
            $this->filter,
            $this->ancestor,
            $method,
        ));
    }

    /**
     * Returns the class information that will be queried.
     *
     * @return    DClassInformation
     */
    protected function getClassInformation()
    {
        if ($this->classInformation === null) {
            $globalRegistry = DGlobalRegistry::load();
            $this->classInformation = $globalRegistry->getHive(DClassInformation::class);
        }

        return $this->classInformation;
    }

    /**
     * Returns a list of class names that match the current search.
     *
     * @return    array    List of qualified class names.
     */
    public function getClassNames()
    {
        // This is a registry dependent cache item, so no need to worry
        // about clearing it as it will invalidate if the registry changes.
        if (DApplicationMode::isProductionMode()) {
            $cacheKey = $this->getCacheKey(__FUNCTION__);
            $publicCache = DPublicCache::load();
            $classNames = $publicCache->retrieve(__CLASS__, $cacheKey);
        } else {
            $publicCache = null;
            $classNames = null;
        }
        if ($classNames === null) {
            // Query the class information object.
            $classInformation = $this->getClassInformation();
            $classNames = $classInformation->getClassNames(
                $this->ancestor,
                $this->filter
            );
            if ($publicCache) {
                $publicCache->set(__CLASS__, $cacheKey, $classNames);
            }
        }

        return $classNames;
    }

    /**
     * Returns the inheritance hierarchy of the provided class.
     *
     * @param    string $qualifiedName    Class to return the hierarchy for.
     * @param    string $until            Qualified name of the class to stop at.
     *                                    If <code>null</code>, the hierarchy will
     *                                    continue until the root.
     *
     * @return    array    Ordered list of class names, with the root ancestor
     *                    at position zero.
     */
    public static function getInheritanceHierarchy($qualifiedName, $until = null)
    {
        $hierarchy = array();
        do {
            array_unshift($hierarchy, $qualifiedName);
            // Check whether we should stop.
            if ($until !== null
                && $qualifiedName === $until
            ) {
                $qualifiedName = false;
            } else {
                $qualifiedName = get_parent_class($qualifiedName);
            }
        } while ($qualifiedName !== false);

        return $hierarchy;
    }

    /**
     * Determines if the specified class is abstract.
     *
     * @param    string $className Name of the class to test.
     *
     * @return    array    List of qualified class names.
     */
    public function isAbstract($className)
    {
        if (self::$abstractClasses === null) {
            // This is a registry dependent cache item, so no need to worry
            // about clearing it as it will invalidate if the registry changes.
            if (DApplicationMode::isProductionMode()) {
                $cacheKey = $this->getCacheKey(__FUNCTION__);
                $publicCache = DPublicCache::load();
                $abstractClasses = $publicCache->retrieve(__CLASS__, $cacheKey);
            } else {
                $publicCache = null;
                $abstractClasses = null;
            }
            if ($abstractClasses === null) {
                $classInformation = $this->getClassInformation();
                $abstractClasses = array_flip(
                    $classInformation->getClassNames(
                        null,
                        self::FILTER_ABSTRACT
                    )
                );
                if ($publicCache) {
                    $publicCache->set(__CLASS__, $cacheKey, $abstractClasses);
                }
            }
            self::$abstractClasses = $abstractClasses;
        }

        return isset(self::$abstractClasses[ $className ]);
    }

    /**
     * Determines if a class name is valid for this query.
     *
     * @param    string $qualifiedName Qualified class name.
     *
     * @return    bool
     */
    public function isValid($qualifiedName)
    {
        return in_array(
            $qualifiedName,
            $this->getClassNames()
        );
    }

    /**
     * Tests a provided qualified class name to determine if this class
     * exists on the current installation.
     *
     * @param    string $qualifiedName The class name to test.
     * @param    string $ancestor      The class ancestor.
     *
     * @return    bool
     */
    public static function isValidClassName($qualifiedName, $ancestor = null)
    {
        if ($qualifiedName === $ancestor) {
            $valid = true;
        } else {
            $query = static::load()
                           ->setFilter();
            if ($ancestor !== null) {
                $query->setAncestor($ancestor);
            }
            $valid = $query->isValid($qualifiedName);
        }

        return $valid;
    }

    /**
     * Allows results to be limited to those that extend a particular class.
     *
     * @param    string $ancestor Qualified name of the ancestor class.
     *
     * @return    static
     * @throws    DInvalidClassNameException    If the provided ancestor
     *                                        class does not exist.
     */
    public function setAncestor($ancestor = null)
    {
        $isValid = DClassQuery::load()
                              ->setFilter()
                              ->isValid($ancestor);
        if (!$isValid) {
            throw new DInvalidClassNameException($ancestor);
        }
        $this->ancestor = $ancestor;

        return $this;
    }

    /**
     * Applies a filter to the query.
     *
     * @param    int $filter      Bitwise filter to apply, can include:
     *                            - {@link DClassQuery::FILTER_ABSTRACT}
     *                            - {@link DClassQuery::FILTER_CONCRETE}
     *                            - {@link DClassQuery::FILTER_LEAF}
     *
     * @return    static
     */
    public function setFilter($filter = 0)
    {
        $this->filter = $filter;

        return $this;
    }
}
