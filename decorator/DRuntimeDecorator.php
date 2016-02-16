<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

use app\decibel\configuration\DApplicationMode;
use app\decibel\registry\DGlobalRegistry;

/**
 * Base dynamic decorator class.
 *
 * @author    Timothy de Paris
 */
abstract class DRuntimeDecorator extends DDecorator
{
    /**
     * Stores information about available decorators once first determined
     * by {@link DRuntimeDecorator::getAvailableDecorators()}
     *
     * @var        array
     */
    private static $availableDecorators = array();

    /**
     * Stores the result of a hierarchy test so it does not need to be repeated.
     *
     * @var        array
     */
    private static $hierarchyTests = array();

    /**
     * Decorates the provided class with the most appropriate type of decorator.
     *
     * @note
     * This method should be called statically against the base decorator class
     * of the desired type. The most appropriate decorator will automatically
     * be selected based on the type of the object to be decorated.
     *
     * @warning
     * In debug mode, the hierarchy of the decorator will be tested against the hierarchy
     * of the decorated object, to ensure that the leaf class correctly extends the parent
     * classes for the decorator.
     *
     * @param    DDecoratable $decorated The object to decorate.
     *
     * @return    static
     * @throws    DMissingDecoratorException    If an appropriate decorator    cannot be found.
     */
    public static function decorate(DDecoratable $decorated)
    {
        $decoratorType = static::getDecoratorFor(get_class($decorated));
        // If no decorator was found, throw an exception.
        if ($decoratorType === null) {
            throw new DMissingDecoratorException(get_called_class(), $decorated);
        }
        $decorator = $decorated->getDecorator($decoratorType);
        if ($decorator === null) {
            $decorator = new $decoratorType($decorated);
            $decorated->setDecorator($decorator);
        }
        if (DApplicationMode::isDebugMode()) {
            static::testHierarchy($decorator, $decorated);
        }

        return $decorator;
    }

    /**
     * Determines the non-abstract decorators available
     * for this run-time decorator type.
     *
     * @return    array    List of qualified names of decorators that extend
     *                    the class this method was called against.
     */
    protected static function getAvailableDecorators()
    {
        $baseDecorator = get_called_class();
        // Build a list of available decorators if this
        // is the first time we have decorated with this class.
        if (!isset(self::$availableDecorators[ $baseDecorator ])) {
            // This isn't stored in the memory cache as benchmarking shows it
            // is quicker to load it directly out of the registry.
            $registry = DGlobalRegistry::load();
            $decoratorInformation = $registry->getHive(DDecoratorInformation::class);
            $availableDecorators = $decoratorInformation->getAvailableDecorators($baseDecorator);
            self::$availableDecorators[ $baseDecorator ] = $availableDecorators;
        }

        return self::$availableDecorators[ $baseDecorator ];
    }

    /**
     * Determines the qualified name of the {@link DRuntimeDecorator} that is most
     * appropriate for the provided class name.
     *
     * @param    string $qualifiedName Qualified name of the class to decorate.
     *
     * @return    string    The decorator, or <code>null</code> if no decorator was found.
     */
    protected static function getDecoratorFor($qualifiedName)
    {
        $decorator = null;
        $available = static::getAvailableDecorators();
        do {
            if (isset($available[ $qualifiedName ])) {
                $decorator = $available[ $qualifiedName ];
                break;
            }
            $qualifiedName = get_parent_class($qualifiedName);
        } while ($qualifiedName);

        return $decorator;
    }

    /**
     * Tests the hierarchy of this decorator to ensure it matches that of the object it decorates.
     *
     * @param    DRuntimeDecorator $decorator The selected decorator.
     * @param    DDecoratable      $decorated The object being decorated.
     *
     * @return    void
     */
    protected static function testHierarchy(DRuntimeDecorator $decorator, DDecoratable $decorated)
    {
        $decoratedClass = get_class($decorated);
        $decoratorClass = get_class($decorator);
        if (!isset(self::$hierarchyTests[ $decoratedClass ][ $decoratorClass ])) {
            self::$hierarchyTests[ $decoratedClass ][ $decoratorClass ] = true;
            $parent = $decoratedClass;
            do {
                $qualifiedName = static::getDecoratorFor($parent);
                if ($qualifiedName !== null
                    && !$decorator instanceof $qualifiedName
                ) {
                    self::$hierarchyTests[ $decoratedClass ][ $decoratorClass ] = false;
                    break;
                }
                $parent = get_parent_class($parent);
            } while ($parent);
        }
        if (!self::$hierarchyTests[ $decoratedClass ][ $decoratorClass ]) {
            throw new DInvalidDecoratorHierarchyException($decorator, $qualifiedName);
        }
    }
}
