<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event\debug;

/**
 * Handles an exception occurring when an attempt is made to subscribe
 * an invalid observer to an event.
 *
 * See @ref events_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        events_exceptions
 */
class DInvalidObserverException extends DEventException
{
    /**
     * Creates a new {@link DInvalidObserverException} object.
     *
     * @param    callable $observer The invalid observer reference.
     *
     * @return    static
     */
    public function __construct($observer)
    {
        parent::__construct(array(
                                'observer' => $this->formatCallable($observer),
                            ));
    }
}
