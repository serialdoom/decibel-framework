<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

use app\decibel\reflection\DReflectionClass;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DRegistryHive;

/**
 * Registers information about Decorators.
 *
 * @author    Timothy de Paris
 */
class DDecoratorInformation extends DRegistryHive
{
    /**
     * Index of runtime decorators within the scope of the registry.
     *
     * @var        array
     */
    protected $runtimeDecorators = array();

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
                'runtimeDecorators' => $this->runtimeDecorators,
            )
        );
    }

    ///@cond INTERNAL
    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'runtimeDecorators';

        return $sleep;
    }
    ///@endcond
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
     * Returns the decorators available for the specified runtime decorator.
     *
     * @return    array
     */
    public function getAvailableDecorators($runtimeDecorator)
    {
        if (isset($this->runtimeDecorators[ $runtimeDecorator ])) {
            $decorators = $this->runtimeDecorators[ $runtimeDecorator ];
        } else {
            $decorators = array();
        }

        return $decorators;
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
        if (!$hive instanceof DDecoratorInformation) {
            return false;
        }
        $this->runtimeDecorators = array_merge_recursive(
            $this->runtimeDecorators,
            $hive->runtimeDecorators
        );

        return true;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        $classInformation = $this->getDependency(DClassInformation::class);
        $runtimeDecorators = $classInformation
            ->getClassNames(DRuntimeDecorator::class);
        $this->runtimeDecorators = array();
        foreach ($runtimeDecorators as $qualifiedName) {
            $this->reflectDecorator($qualifiedName);
        }
    }

    /**
     * Reflects a decorator and adds information to the runtime decorators list.
     *
     * @param    string $qualifiedName
     *
     * @return    void
     */
    protected function reflectDecorator($qualifiedName)
    {
        $decoratedClass = $qualifiedName::getDecoratedClass();
        $reflection = new DReflectionClass($qualifiedName);
        $parents = $reflection->getParentNames();
        $parents[] = $qualifiedName;
        foreach ($parents as $parent) {
            if ($parent !== DDecorator::class
                && $parent !== DRuntimeDecorator::class
            ) {
                $this->runtimeDecorators[ $parent ][ $decoratedClass ] = $qualifiedName;
            }
        }
    }
}
