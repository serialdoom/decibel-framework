<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event;

use app\decibel\event\debug\DInvalidObserverException;

/**
 * Represents a subscription to an event.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        events
 */
class DEventSubscription
{
    /**
     * The observer subscribed to the event.
     *
     * @var        callable
     */
    protected $observer;
    /**
     * Additional data that will be passed to the observer.
     *
     * @var        mixed
     */
    protected $eventData;

    /**
     * Creates a new event subscription.
     *
     * @param    callable $observer       The observer. This must be a callable
     *                                    type (see http://php.net/is_callable)
     * @param    mixed    $eventData      Additional data that will be passed
     *                                    to the observer.
     *
     * @throws    DInvalidObserverException    If the provided observer is not
     *                                        a valid callable.
     * @return    static
     */
    public function __construct($observer, $eventData = null)
    {
        // Make sure the subscriber is callable.
        if (!is_callable($observer)) {
            throw new DInvalidObserverException($observer);
        }
        $this->observer = $observer;
        $this->eventData = $eventData;
    }

    /**
     * Returns additional data that will be passed to the observer.
     *
     * @return    callable
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * Returns the observer subscribed to the event.
     *
     * @return    callable
     */
    public function getObserver()
    {
        return $this->observer;
    }
}
