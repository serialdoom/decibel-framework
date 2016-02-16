<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

use app\decibel\reflection\DReflectionClass;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DRegistryHive;

/**
 * Registers information about adpaters.
 *
 * @author    Timothy de Paris
 */
class DAdapterInformation extends DRegistryHive
{
    /**
     * Index of runtime adapters within the scope of the registry.
     *
     * @var        array
     */
    protected $runtimeAdapters = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array_merge(
            parent::generateDebug(),
            array(
                'runtimeAdapters' => $this->runtimeAdapters,
            )
        );
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'runtimeAdapters';

        return $sleep;
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);

        return $fileInformation->getChecksum();
    }

    /**
     * Returns the adapters available for the specified runtime adapters.
     *
     * @param    string $runtimeAdapter Qualified name of the runtime adapter.
     *
     * @return    array
     */
    public function getAvailableAdapters($runtimeAdapter)
    {
        if (isset($this->runtimeAdapters[ $runtimeAdapter ])) {
            $adapters = $this->runtimeAdapters[ $runtimeAdapter ];
        } else {
            $adapters = array();
        }

        return $adapters;
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(
            DFileInformation::class,
            DClassInformation::class,
        );
    }

    /**
     * Returns a version number indicating the format of the registry.
     *
     * @return    int
     */
    public function getFormatVersion()
    {
        return 1;
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool
     */
    public function merge(DRegistryHive $hive)
    {
        if ($hive instanceof DAdapterInformation) {
            $this->runtimeAdapters = array_merge_recursive(
                $this->runtimeAdapters,
                $hive->runtimeAdapters
            );
            $merged = true;
        } else {
            $merged = false;
        }

        return $merged;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        /* @var $classInformation DClassInformation */
        $classInformation = $this->getDependency(DClassInformation::class);
        $runtimeAdapters = $classInformation->getClassNames(DRuntimeAdapter::class);

        $this->runtimeAdapters = array();

        foreach ($runtimeAdapters as $qualifiedName) {
            $this->reflectAdapter($qualifiedName);
        }
    }

    /**
     * Reflects an adapter and adds information to the runtime adapters list.
     *
     * @param    string $qualifiedName
     *
     * @return    void
     */
    protected function reflectAdapter($qualifiedName)
    {
        $adapatedClass = $qualifiedName::getAdaptableClass();
        $reflection = new DReflectionClass($qualifiedName);
        $parents = $reflection->getParentNames();
        $parents[] = $qualifiedName;
        foreach ($parents as $parent) {
            $this->runtimeAdapters[ $parent ][ $adapatedClass ] = $qualifiedName;
        }
    }
}
