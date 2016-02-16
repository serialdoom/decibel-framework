<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when requesting an invalid registry hive
 * dependency.
 *
 * @author    Timothy de Paris
 */
class DInvalidDependencyException extends DRegistryException
{
    /**
     * Creates a new {@link DInvalidDependencyException}.
     *
     * @param    DRegistryHive $hive          Hive from which the dependency
     *                                        was requested.
     * @param    string        $dependency    Qualified name of the requested
     *                                        dependency.
     *
     * @return    static
     */
    public function __construct(DRegistryHive $hive, $dependency)
    {
        parent::__construct(array(
                                'hive'       => get_class($hive),
                                'dependency' => $dependency,
                            ));
    }
}
