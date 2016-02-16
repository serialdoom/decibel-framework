<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

use app\decibel\configuration\DApplicationMode;
use app\decibel\registry\DGlobalRegistry;

/**
 * Implements an adapter that selects the most appropriate non-abstract implementation
 * at runtime, depending on the {@link DAdaptable} class it is adapting.
 *
 * @author    Timothy de Paris
 */
trait DRuntimeAdapter
{
    /**
     * The adapted object instance.
     *
     * @var        DAdaptable
     */
    protected $adaptee;
    /**
     * Stores information about available adapters once first determined
     * by {@link DRuntimeAdapter::getAvailableAdapters()}
     *
     * @var        array
     */
    private static $availableAdapters = array();
    /**
     * Stores the result of a hierarchy test so it does not need to be repeated.
     *
     * @var        array
     */
    private static $hierarchyTests = array();

    /**
     * Initialises an adapter.
     *
     * @param    DAdaptable $adapteee The object instance to be adapted.
     *
     */
    protected function __construct(DAdaptable $adapteee)
    {
        $this->adaptee = $adapteee;
    }

    /**
     * Returns an adapter for the provided object instance.
     *
     * @note
     * This method should be called statically against the base adapter class
     * of the desired type. The most appropriate adapter will automatically
     * be selected based on the type of the object to be adapted.
     *
     * @warning
     * In debug mode, the hierarchy of the adapter will be tested against the hierarchy
     * of the adapted object, to ensure that the leaf class correctly extends the parent
     * classes for the adapter.
     *
     * @param    DAdaptable $adaptee The object instance to be adapted.
     *
     * @return    static
     * @throws    DInvalidAdapterException    If this adapter cannot be used
     *                                        to adapt the provided object instance.
     * @throws    DMissingAdapterException    If an appropriate adapter cannot be found.
     */
    public static function adapt(DAdaptable $adaptee)
    {
        $adapterType = static::getAdapterFor(get_class($adaptee));
        // If no adapter was found, throw an exception.
        if ($adapterType === null) {
            throw new DMissingAdapterException(get_called_class(), $adaptee);
        }
        $adapter = $adaptee->getAdapter($adapterType);
        if ($adapter === null) {
            $adapter = new $adapterType($adaptee);
            $adaptee->setAdapter($adapter);
        }
        if (DApplicationMode::isDebugMode()) {
            static::testHierarchy($adapter, $adaptee);
        }

        return $adapter;
    }

    /**
     * Returns the object instance that is being adapted by this adapter.
     *
     * @return    DAdaptable
     */
    public function getAdaptee()
    {
        return $this->adaptee;
    }

    /**
     * Determines the non-abstract adapters available for this run-time adapter type.
     *
     * @return    array    List of qualified names of adapters that extend
     *                    the class this method was called against.
     */
    protected static function getAvailableAdapters()
    {
        $baseAdapter = get_called_class();
        // Build a list of available adapters if this
        // is the first time we have adapted with this class.
        if (!isset(self::$availableAdapters[ $baseAdapter ])) {
            // This isn't stored in the memory cache as benchmarking shows it
            // is quicker to load it directly out of the registry.
            /* @var $adapterInformation DAdapterInformation */
            $registry = DGlobalRegistry::load();
            $adapterInformation = $registry->getHive(DAdapterInformation::class);
            $availableAdapters = $adapterInformation->getAvailableAdapters($baseAdapter);
            self::$availableAdapters[ $baseAdapter ] = $availableAdapters;
        }

        return self::$availableAdapters[ $baseAdapter ];
    }

    /**
     * Determines the qualified name of the {@link DRuntimeAdapter} that is most
     * appropriate for the provided class name.
     *
     * @param    string $qualifiedName Qualified name of the class to adapt.
     *
     * @return    string    The adapter, or <code>null</code> if no adapter was found.
     */
    protected static function getAdapterFor($qualifiedName)
    {
        $adapter = null;
        $available = static::getAvailableAdapters();
        do {
            if (isset($available[ $qualifiedName ])) {
                $adapter = $available[ $qualifiedName ];
                break;
            }
            $qualifiedName = get_parent_class($qualifiedName);
        } while ($qualifiedName);

        return $adapter;
    }

    /**
     * Tests the hierarchy of this adapter to ensure it matches that of the object it adapts.
     *
     * @param    DAdapter   $adapter The selected adapter.
     * @param    DAdaptable $adaptee The object being adapted.
     *
     * @throws DInvalidAdapterHierarchyException
     */
    protected static function testHierarchy(DAdapter $adapter, DAdaptable $adaptee)
    {
        $adapteeClass = get_class($adaptee);
        $adapterClass = get_class($adapter);
        if (!isset(self::$hierarchyTests[ $adapteeClass ][ $adapterClass ])) {
            self::$hierarchyTests[ $adapteeClass ][ $adapterClass ] = true;
            $parent = $adapteeClass;
            do {
                $qualifiedName = static::getAdapterFor($parent);
                if ($qualifiedName !== null
                    && !$adapter instanceof $qualifiedName
                ) {
                    self::$hierarchyTests[ $adapteeClass ][ $adapterClass ] = false;
                    break;
                }
                $parent = get_parent_class($parent);
            } while ($parent);
        }
        if (!self::$hierarchyTests[ $adapteeClass ][ $adapterClass ]) {
            throw new DInvalidAdapterHierarchyException($adapter, $qualifiedName);
        }
    }
}
