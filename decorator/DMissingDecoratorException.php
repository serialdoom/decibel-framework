<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\decorator;

/**
 * Handles an exception occurring when an appropriate {@link DDecorator}
 * cannot be found with which to decorate a {@link DDecoratable} object.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DMissingDecoratorException extends DDecoratorException
{
    /**
     * Creates a new {@link DMissingDecoratorException}.
     *
     * @param    string       $decorator   Qualified name of the decorator.
     * @param    DDecoratable $decoratable Object to be decorated.
     *
     * @return    static
     */
    public function __construct($decorator, DDecoratable $decoratable)
    {
        parent::__construct(array(
                                'decorator'   => $decorator,
                                'decoratable' => get_class($decoratable),
                            ));
    }
}
