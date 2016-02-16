<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event\debug;

/**
 * Handles an exception occurring when an attempt is made to subscribe
 * an non-existent event.
 *
 * See @ref events_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        events_exceptions
 */
class DInvalidEventException extends DEventException
{
    /**
     * Creates a new {@link DInvalidEventException}.
     *
     * @param    string $event      Qualified name of the event.
     * @param    string $dispatcher Qualified name of the event dispatcher.
     *
     * @return    static
     */
    public function __construct($event, $dispatcher)
    {
        parent::__construct(array(
                                'event'      => $event,
                                'dispatcher' => $dispatcher,
                            ));
    }
}
