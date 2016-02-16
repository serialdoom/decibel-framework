<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Handles an exception occurring when the requested {@link DAdapter}
 * cannot be used to adapt a {@link DAdaptable} object.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DInvalidAdapterException extends DAdapterException
{
    /**
     * Creates a new {@link DInvalidAdapterException}.
     *
     * @param    string     $adaptor Qualified name of the adaptor.
     * @param    DAdaptable $adaptee Object to be adapted.
     *
     */
    public function __construct($adaptor, DAdaptable $adaptee)
    {
        parent::__construct(array(
                                'adaptor' => $adaptor,
                                'adaptee' => get_class($adaptee),
                            ));
    }
}
