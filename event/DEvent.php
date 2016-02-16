<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event;

use app\decibel\debug\DErrorHandler;
use app\decibel\event\debug\DMissingEventParameterException;
use app\decibel\event\DEventReflection;
use app\decibel\model\field\DField;
use app\decibel\reflection\DReflectable;
use app\decibel\utility\DResult;
use app\decibel\utility\DUtilityData;

/**
 * Base class for events that are able to be triggered by a {@link DEventDispatcher}.
 *
 * @author         Timothy de Paris
 * @ingroup        events
 */
abstract class DEvent extends DUtilityData implements DReflectable
{
    /**
     * The dispatcher that produced the event.
     *
     * @var        DDispatchable
     */
    protected $dispatcher;
    /**
     * The name of the event produced by the dispatcher.
     *
     * @var        string
     */
    protected $event;
    /**
     * Whether propagation of this event has been stopped.
     *
     * @var        bool
     */
    private $propagationStopped = false;
    /**
     * Cummulative result from all observers.
     *
     * @var        DResult
     */
    protected $result;

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Provides a reflection describing this event.
     *
     * @return    DEventReflection
     */
    public static function getReflection()
    {
        return new DEventReflection(get_called_class());
    }

    /**
     * Returns the event result.
     *
     * @return    DResult
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Determines if propagation of this event had been stopped.
     *
     * @return    bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * Merges a provided result into the current list of messages and errors.
     *
     * @param    DResult $result   The result to merge.
     * @param    array   $messages Current list of messages.
     * @param    array   $errors   Current list of errors.
     *
     * @return    void
     */
    protected function mergeResult(DResult $result = null,
                                   array &$messages = array(), array &$errors = array())
    {
        if ($result === null) {
            return;
        }
        if ($result->isSuccessful()) {
            $messages = array_merge($messages, $result->getMessages()->toArray());
        } else {
            $errors = array_merge($errors, $result->getMessages()->toArray());
        }
    }

    /**
     * Notifies the provided subscribers.
     *
     * @param    array $subscriptions     List of {@link DEventSubscription}
     *                                    objects.
     *
     * @return    void
     * @todo    Add support for DResult::TYPE_WARNING
     */
    final public function notify(array $subscriptions)
    {
        // Check that the event is ready to notify!
        $this->validate();
        $errors = array();
        $messages = array();
        if ($this->result) {
            $this->mergeResult($this->result, $messages, $errors);
        }
        foreach ($subscriptions as $subscription) {
            /* @var $subscription DEventSubscription */
            $this->notifySubscriber(
                $subscription,
                $messages,
                $errors
            );
            // Stop notification if requested.
            if ($this->propagationStopped) {
                break;
            }
        }
        if ($errors) {
            $resultType = DResult::TYPE_ERROR;
        } else {
            $resultType = DResult::TYPE_SUCCESS;
        }
        $this->result = new DResult();
        $this->result->setSuccess($resultType);
        $this->result->addMessages(array_merge($errors, $messages));
    }

    /**
     * Notifies a single subscriber.
     *
     * @param    DEventSubscription $subscription The event subscription.
     * @param    array              $messages     List of message produced during this event.
     * @param    array              $errors       List of errors produced during this event.
     *
     * @return    void
     */
    final protected function notifySubscriber(DEventSubscription $subscription,
                                              array &$messages, array &$errors)
    {
        // Notify the subscriber.
        $result = call_user_func(
            $subscription->getObserver(),
            $this,
            $subscription->getEventData()
        );
        // Append to results.
        if (is_array($result)) {
            $errors = array_merge($errors, $result);
        } else {
            $this->mergeResult($result, $messages, $errors);
        }
    }

    /**
     * Sets the dispatcher of this event.
     *
     * @param    DDispatchable $dispatcher
     *
     * @return    void
     */
    public function setDispatcher(DDispatchable $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Instructs the dispatcher to prevent notification of the event
     * to any subsequent subscribers.
     *
     * @return    static
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;

        return $this;
    }

    /**
     * This function is called before subscribers to the event are notified.
     *
     * @return    bool    Whether the event is ready for notification.
     *                    If <code>false</code> if returned, notification will
     *                    be cancelled.
     * @throws    DMissingEventParameterException    If a field value
     *                                            has not been provided.
     */
    public function validate()
    {
        // Check all fields have been assigned.
        $valid = true;
        foreach ($this->getFields() as $name => $field) {
            /* @var $field DField */
            // Check if a value has been assigned for required fields.
            $check = $field->checkValue($this->getFieldValue($name));
            if (!$check->isSuccessful()) {
                $valid = false;
                DErrorHandler::throwException(
                    new DMissingEventParameterException(
                        $name,
                        get_called_class()
                    )
                );
            }
        }

        return $valid;
    }
}
