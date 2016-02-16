<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Handles an exception occurring when an appropriate {@link DAdapter}
 * cannot be found with which to adapt a {@link DAdaptable} object.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DMissingAdapterException extends DAdapterException
{
    /**
     * Creates a new {@link DMissingAdapterException}.
     *
     * @param    string     $adapter Qualified name of the adapter.
     * @param    DAdaptable $adaptee Object to be adapted.
     *
     */
    public function __construct($adapter, DAdaptable $adaptee)
    {
        parent::__construct(array(
                                'adapter' => $adapter,
                                'adaptee' => get_class($adaptee),
                            ));
    }
}
