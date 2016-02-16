<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

/**
 * Handles an exception occurring when a {@link DRuntimeDecorator}
 * does not match the hierarchy of the object it decorates.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DInvalidDecoratorHierarchyException extends DDecoratorException
{
    /**
     * Creates a new {@link DInvalidDecoratorException}.
     *
     * @param    DDecorator $decorator The decorator.
     * @param    string     $missing   Qualified name of the missing decorator.
     *
     * @return    static
     */
    public function __construct(DDecorator $decorator, $missing)
    {
        parent::__construct(array(
                                'decorator' => get_class($decorator),
                                'missing'   => $missing,
                            ));
    }
}
