<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Allows a class to be adapted.
 *
 * @note
 * This interface support caching of adapters, to reduce overhead when using adapters multiple
 * times within a single request.
 *
 * @author    Timothy de Paris
 */
interface DAdaptable
{
    /**
     * Retrieves a cached adapter for this object.
     *
     * @param    string $qualifiedName Qualified name of the adapter to retrieve.
     *
     * @return    DAdapter    The adapter, or <code>null</code> if the adapter has not
     *                        previously been loaded.
     */
    public function getAdapter($qualifiedName);

    /**
     * Caches an adapter that has been loaded for this object.
     *
     * @param    DAdapter $adapter
     *
     * @return    void
     */
    public function setAdapter(DAdapter $adapter);
}
