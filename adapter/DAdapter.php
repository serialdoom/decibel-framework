<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Interface for implementation of the adapter pattern.
 *
 * Adapters allows additional functionality to be added to a class without increasing
 * the complexity of the inner class (the adaptee).
 *
 * @author    Timothy de Paris
 */
interface DAdapter
{
    /**
     * Returns an adapter for the provided object instance.
     *
     * @note
     * This method should be called statically against the base
     * adapter class of the desired type.
     *
     * @param    DAdaptable $adaptee The object instance to be adapted.
     *
     * @return    static
     * @throws    DInvalidAdapterException    If this adapter cannot be used
     *                                        to adapt the provided object instance.
     */
    public static function adapt(DAdaptable $adaptee);

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass();

    /**
     * Returns the object instance that is being adapted by this adapter.
     *
     * @return    DAdaptable
     */
    public function getAdaptee();
}
