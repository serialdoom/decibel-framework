<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

/**
 * Provides caching functionality for decoratable objects to improve performance.
 *
 * @author    Timothy de Paris
 */
trait DDecoratorCache
{
    /**
     * Cache of loaded decorators for this object.
     *
     * @var        array
     */
    private $decorators = array();

    /**
     * Retrieves a cached decorator for this object.
     *
     * @param    string $qualifiedName Qualified name of the decorator to retrieve.
     *
     * @return    DDecorator    The decorator, or <code>null</code> if the decorator has not
     *                        previously been loaded.
     */
    public function getDecorator($qualifiedName)
    {
        if (isset($this->decorators[ $qualifiedName ])) {
            $decorator = $this->decorators[ $qualifiedName ];
        } else {
            $decorator = null;
        }

        return $decorator;
    }

    /**
     * Caches a decorator that has been loaded for this object.
     *
     * @param    DDecorator $decorator
     *
     * @return    void
     */
    public function setDecorator(DDecorator $decorator)
    {
        $this->decorators[ get_class($decorator) ] = $decorator;
    }
}
