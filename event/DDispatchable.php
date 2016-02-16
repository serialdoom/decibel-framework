<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event;

use app\decibel\event\debug\DDuplicateSubscriptionException;
use app\decibel\event\debug\DInvalidEventException;
use app\decibel\event\debug\DInvalidObserverException;

/**
 * A class that can dispatch events to subscribers.
 *
 * @author        Timothy de Paris
 */
interface DDispatchable
{
    /**
     * Returns the name of the default event for this dispatcher.
     *
     * This function must be overriden by extending classes.
     * See the @ref events_dispatchers Developer Guide for further information.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent();

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * This function must be overriden by extending classes.
     * See the @ref events_dispatchers Developer Guide for further information.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents();

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
                                             $event = null, $eventData = null);

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
    //	protected function notifyObservers(DEvent $event);
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
    public static function unsubscribeObserver($observer, $event = null);
}
