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
class DDuplicateSubscriptionException extends DEventException
{
    /**
     * Creates a new {@link DDuplicateSubscriptionException}.
     *
     * @param    callable $observer   The subscribing observer.
     * @param    string   $event      Qualified name of the event.
     * @param    string   $dispatcher Qualified name of the event dispatcher.
     *
     * @return    static
     */
    public function __construct($observer, $event, $dispatcher)
    {
        parent::__construct(array(
                                'observer'   => $this->formatCallable($observer),
                                'event'      => $event,
                                'dispatcher' => $dispatcher,
                            ));
    }
}
