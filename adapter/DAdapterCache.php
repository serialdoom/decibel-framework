<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\adapter;

/**
 * Implementation of the {@link DAdaptable} interface which provides caching
 * of adapters to improve performance.
 *
 * @author    Timothy de Paris
 */
trait DAdapterCache
{
    /**
     * Cache of loaded adapters for this object.
     *
     * @var        array
     */
    private $adapters = array();

    /**
     * Retrieves a cached adapter for this object.
     *
     * @param    string $qualifiedName Qualified name of the adapter to retrieve.
     *
     * @return    DAdapter    The adapter, or <code>null</code> if the adapter has not
     *                        previously been loaded.
     */
    public function getAdapter($qualifiedName)
    {
        if (isset($this->adapters[ $qualifiedName ])) {
            $adapter = $this->adapters[ $qualifiedName ];
        } else {
            $adapter = null;
        }

        return $adapter;
    }

    /**
     * Caches an adapter that has been loaded for this object.
     *
     * @param    DAdapter $adapter
     *
     * @return    void
     */
    public function setAdapter(DAdapter $adapter)
    {
        $this->adapters[ get_class($adapter) ] = $adapter;
    }
}
