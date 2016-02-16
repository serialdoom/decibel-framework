<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event;

use app\decibel\application\DAppManager;
use app\decibel\configuration\DApplicationMode;
use app\decibel\event\debug\DDuplicateSubscriptionException;
use app\decibel\event\debug\DInvalidEventException;
use app\decibel\event\debug\DInvalidObserverException;
use app\decibel\event\DEvent;
use app\decibel\event\DEventSubscription;
use app\decibel\http\DHttpResponse;
use app\decibel\registry\DClassQuery;

/**
 * Adds event dispatching functionality to an implementing class.
 *
 * @author        Timothy de Paris
 */
trait DEventDispatcher
{
    /**
     * Observers subscribed to events for all event dispatchers.
     *
     * @var        array
     */
    protected static $observers;

    /**
     * Clears the internal cache of observer informaiton.
     *
     * @return    void
     */
    public static function clearObserverCache()
    {
        self::$observers = null;
    }

    /**
     * Retrieves a pointer to the appropriate section of the registration
     * array for the specified dispatcher and event.
     *
     * @param    string $dispatcher   Qualified name of the dispatcher.
     * @param    string $event        If provided, the return value will point
     *                                to the event section of the registrations
     *                                array for the specified dispatcher.
     *
     * @return    array
     */
    protected static function &getObserverRegistrations($dispatcher, $event = null)
    {
        // Check that the class holds a pointer to the appropriate
        // observer subscription registration array.
        if (!isset(self::$observers[ $dispatcher ])) {
            self::$observers[ $dispatcher ] =& DAppManager::getRegistration(
                self::class,
                'observer',
                $dispatcher
            );
        }
        if (!isset(self::$observers[ $dispatcher ])) {
            self::$observers[ $dispatcher ] = array();
        }
        if ($event === null) {
            return self::$observers[ $dispatcher ];
        }
        if (!isset(self::$observers[ $dispatcher ][ $event ])) {
            self::$observers[ $dispatcher ][ $event ] = array();
        }

        return self::$observers[ $dispatcher ][ $event ];
    }

    /**
     * Determines if the specified observer exists in the provided list
     * of subscriptions.
     *
     * @param    array    $subscriptions      List of {@link DEventSubscription}
     *                                        objects.
     * @param    callable $observer           The observer. This must be a callable
     *                                        type (see http://php.net/is_callable)
     * @param    mixed    $eventData          If provided, a matched subscription
     *                                        must also have this event data.
     *
     * @return    string        The key of the matched subscription within
     *                        the provided array, or <code>null</code>
     *                        if no match is found.
     */
    protected static function isSubscribed(array $subscriptions,
                                           callable $observer, $eventData = null)
    {
        foreach ($subscriptions as $key => $subscription) {
            /* @var $subscription DEventSubscription */
            if ($subscription->getObserver() !== $observer) {
                continue;
            }
            // If event data was provided, match this also.
            if ($eventData !== null
                && $subscription->getEventData() !== $eventData
            ) {
                continue;
            }

            return $key;
        }

        return null;
    }

    /**
     * Subscribes an observer to an event produced by this dispatcher.
     *
     * @param    callable $observer       The observer. This must be a callable
     *                                    type (see http://php.net/is_callable)
     * @param    string   $event          The name of the event produced by this
     *                                    dispatcher that the subscriber wants
     *                                    to be notified of. If not provided,
     *                                    the observer will be subscribed to
     *                                    the default event for this dispatcher.
     * @param    mixed    $eventData      Additional data that will be passed
     *                                    to the handler. This will be provided
     *                                    as the handler's second parameter.
     *
     * @return    void
     * @throws    DInvalidObserverException        If the provided observer is not
     *                                            a valid callable.
     * @throws    DInvalidEventException            If the specified event is not
     *                                            produced by this dispatcher.
     * @throws    DDuplicateSubscriptionException    If the observer is already
     *                                            subscribed.
     */
    public static function subscribeObserver(callable $observer,
                                             $event = null, $eventData = null)
    {
        // Create the subscription.
        $subscription = new DEventSubscription(
            $observer,
            $eventData
        );
        // Validate the requested event.
        static::validateEvent($event);
        // Determine qualified name of the dispatcher.
        $qualifiedName = get_called_class();
        // Get a pointer to the appropriate observer registration array.
        $subscriptions =& self::getObserverRegistrations($qualifiedName);
        // Check that the observer is not already subscribed.
        if (isset($subscriptions[ $event ])
            && self::isSubscribed($subscriptions[ $event ], $observer, $eventData) !== null
        ) {
            throw new DDuplicateSubscriptionException($observer, $event, $qualifiedName);
        }
        // Add registration.
        $subscriptions[ $event ][] = $subscription;
        DAppManager::addRegistration(
            self::class,
            'observer',
            $subscriptions,
            $qualifiedName
        );
    }

    /**
     * Notifies subscribers of an event.
     *
     * @param    DEvent $event The event to notify obvservers about.
     *
     * @return    DResult    The cummulative result of the notification,
     *                    or <code>null</code> if there are no observers,
     *                    or no observers returned a result.
     * @throws    DInvalidEventException    If the specified event is not
     *                                    produced by this dispatcher.
     */
    protected function notifyObservers(DEvent $event)
    {
        // Check that this is a valid event.
        $eventName = get_class($event);
        static::validateEvent($eventName);
        // Assign the dispatcher to the event.
        $event->setDispatcher($this);
        // Determine the hierarchy of this event dispatcher.
        $hierarchy = DClassQuery::getInheritanceHierarchy(
            get_called_class(),
            self::class
        );
        $httpResponse = null;
        foreach (array_reverse($hierarchy) as $qualifiedName) {
            // Check if there are any subscriptions.
            $subscriptions =& self::getObserverRegistrations($qualifiedName, $eventName);
            if (!$subscriptions) {
                continue;
            }
            try {
                $event->notify($subscriptions);
                // Catch any redirects issued by event handler.
            } catch (DHttpResponse $httpResponse) {
                // Hold onto the redirect and issue it once all subscribers
                // have finished executing. If more than one subscriber issues
                // a redirect, the last redirect issued will win.
            }
            // If propagation was stopped, don't continue through the hierarchy.
            if ($event->isPropagationStopped()) {
                break;
            }
        }
        // If a redirect was issued, throw it now that everyone
        // has had a chance to be
        if ($httpResponse !== null) {
            throw $httpResponse;
        }

        return $event->getResult();
    }

    /**
     * Un-subscribes an observer from an event produced by this dispatcher.
     *
     * @param    callable $observer       The observer. This must be a callable
     *                                    type (see http://php.net/is_callable)
     * @param    string   $event          The name of the event produced by this
     *                                    dispatcher that the subscriber no longer
     *                                    wants to be notified of. If not provided,
     *                                    the observer will be un-subscribed from
     *                                    the default event for this dispatcher.
     *
     * @return    bool    <code>true</code> if the observer was unsubscribed,
     *                    <code>false</code> if the observer was not subscribed.
     * @throws    DInvalidObserverException    If the provided observer is not
     *                                        a valid callable.
     * @throws    DInvalidEventException        If the specified event is not
     *                                        produced by this dispatcher.
     */
    public static function unsubscribeObserver($observer, $event = null)
    {
        // Make sure the subscriber is callable.
        if (!is_callable($observer)) {
            throw new DInvalidObserverException($observer);
        }
        // Validate the requested event.
        static::validateEvent($event);
        // Determine qualified name of the dispatcher.
        $qualifiedName = get_called_class();
        // Get a pointer to the appropriate observer registration array.
        $subscriptions =& self::getObserverRegistrations($qualifiedName);
        // Check if the observer is subscribed.
        $success = false;
        if (isset($subscriptions[ $event ])) {
            $key = self::isSubscribed($subscriptions[ $event ], $observer);
            if ($key !== null) {
                unset($subscriptions[ $event ][ $key ]);
                // Re-index the array (otherwise the key won't be used again.
                $subscriptions[ $event ] = array_values($subscriptions[ $event ]);
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Validates an event name for this dispatcher.
     *
     * @param    string $eventName    Pointer to the event name.
     *                                If this value is <code>null</code>, the name
     *                                of the default event for this dispatcher
     *                                will be returned in this pointer.
     *
     * @return    void
     * @throws    DInvalidEventException    If the event name is not valid
     *                                    for this dispatcher.
     */
    protected static function validateEvent(&$eventName)
    {
        // Determine the default event.
        if ($eventName === null) {
            $eventName = static::getDefaultEvent();
        }
        // Check that this is a valid event.
        if (!DApplicationMode::isProductionMode()
            && !in_array($eventName, static::getEvents())
        ) {
            throw new DInvalidEventException($eventName, get_called_class());
        }
    }
}
