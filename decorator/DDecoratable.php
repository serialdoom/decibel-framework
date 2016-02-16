<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

/**
 * Allows a class to be decorated.
 *
 * @author    Timothy de Paris
 */
interface DDecoratable
{
    /**
     * Retrieves a cached decorator for this object.
     *
     * @param    string $qualifiedName Qualified name of the decorator to retrieve.
     *
     * @return    DDecorator    The decorator, or <code>null</code> if the decorator has not
     *                        previously been loaded.
     */
    public function getDecorator($qualifiedName);

    /**
     * Caches a decorator that has been loaded for this object.
     *
     * @param    DDecorator $decorator
     *
     * @return    void
     */
    public function setDecorator(DDecorator $decorator);
}
