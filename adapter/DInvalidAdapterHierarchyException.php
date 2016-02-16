<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Handles an exception occurring when a {@link DRuntimeAdapter}
 * does not match the hierarchy of the object it adapts.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DInvalidAdapterHierarchyException extends DAdapterException
{
    /**
     * Creates a new {@link DInvalidAdapterHierarchyException}.
     *
     * @param    DAdapter $adapter The adapter.
     * @param    string   $missing Qualified name of the missing adapter.
     */
    public function __construct(DAdapter $adapter, $missing)
    {
        parent::__construct(array(
                                'adapter' => get_class($adapter),
                                'missing' => $missing,
                            ));
    }
}
