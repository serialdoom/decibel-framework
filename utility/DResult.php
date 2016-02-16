<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\utility;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\regional\DLabel;
use app\decibel\utility\DUtilityData;
use app\DecibelCMS\Utility\DResultFormatter;
use stdClass;

/**
 * The Result object is used by Decibel objects when passing the results of
 * an action.
 *
 * The object contains functions to set, retrieve and display action results.
 *
 * @author        Timothy de Paris
 */
class DResult extends DUtilityData implements DAdaptable
{
    use DAdapterCache;

    /**
     * Error result type.
     *
     * This indicates that the associated action was unable to be completed.
     *
     * @var        string
     */
    const TYPE_ERROR = 'error';

    /**
     * Success result type.
     *
     * This indicates that the associated action was completed with no issues.
     *
     * @var        string
     */
    const TYPE_SUCCESS = 'success';

    /**
     * Warning result type.
     *
     * This indicates that the associated action was completed however one
     * or more non-fatal issues were encountered.
     *
     * @var        string
     */
    const TYPE_WARNING = 'warning';

    /** @var array List of available result types. */
    protected static $types = array(
        self::TYPE_SUCCESS => 'Success',
        self::TYPE_WARNING => 'Warning',
        self::TYPE_ERROR   => 'Error',
    );

    /** @var string The action that was performed. */
    protected $action;

    /** @var mixed Additional data provided with the result. */
    protected $data;

    /**
     * Contains one or more messages explaining the result.
     * @var DResultMessages
     */
    protected $messages;

    /**
     * The subject of the action represented by this result.
     * @var string
     */
    protected $subject;

    /**
     * The result type.
     *
     * One of:
     * - {@link DResult::TYPE_ERROR}
     * - {@link DResult::TYPE_SUCCESS}
     * - {@link DResult::TYPE_WARNING}
     *
     * @var string
     */
    protected $type;

    /**
     * The result title message, if overriden using {@link DResult::setTitle()}.
     *
     * @var string|DLabel
     */
    protected $title;

    /**
     * Creates a new result object.
     *
     * @param    string $subject      The subject of the action this result represents.
     * @param    string $action       The action that was performed.
     * @param    string $type         Whether or not this is a successful result.
     *                                Must be one of {@link DResult::TYPE_ERROR},
     *                                {@link DResult::TYPE_SUCCESS} or
     *                                {@link DResult::TYPE_WARNING}.
     * @param    mixed  $messages     A string, {@link app::decibel::regional::DLabel DLabel}
     *                                instance or an array of strings or
     *                                {@link app::decibel::regional::DLabel DLabel}
     *                                instances.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided type is invalid.
     * @throws    DInvalidParameterValueException    If an invalid messages value is provided.
     */
    public function __construct($subject = null, $action = null,
                                $type = self::TYPE_SUCCESS, $messages = array())
    {
        $this->data = array();
        $this->subject = $subject;
        $this->action = $action;
        $this->type = $this->normaliseType($type);
        // Make array from string or DLabel message if required.
        if (!is_array($messages)) {
            $messages = array($messages);
        }
        $this->messages = new DResultMessages();
        $this->messages->addMessages($messages);
    }

    ///@cond INTERNAL
    /**
     * Returns an HTML representation of the result.
     *
     * @return    string
     * @deprecated    In favour of {@link app::DecibelCMS::Utility::DResultFormatter DResultFormatter}
     */
    public function __toString()
    {
        DErrorHandler::notifyDeprecation(
            'app\decibel\utility\DResult::__toString()',
            'app\DecibelCMS\Utility\DResultFormatter'
        );
        $adapter = DResultFormatter::adapt($this);

        return $adapter->formatAsHtml();
    }
    ///@endcond
    /**
     * Adds a message to this result's list of messages.
     *
     * @param    mixed  $message      The message to add. This can be a string
     *                                or a {@link app::decibel::regional::DLabel DLabel}
     *                                object.
     * @param    string $fieldName    Name of the field the message relates to,
     *                                if any.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If an invalid message value is provided.
     */
    public function addMessage($message, $fieldName = null)
    {
        $this->messages->addMessage($message, $fieldName);

        return $this;
    }

    /**
     * Adds a single message or an array of messages to this result's list of messages.
     *
     * @param    array  $messages     A string, {@link app::decibel::regional::DLabel DLabel}
     *                                instance or an array of strings or
     *                                {@link app::decibel::regional::DLabel DLabel}
     *                                instances.
     * @param    string $fieldName    The field messages apply to, or <code>null</code>
     *                                if fields are supplied per message as keys
     *                                of the messages array.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If an invalid message value is provided.
     */
    public function addMessages(array $messages, $fieldName = null)
    {
        $this->messages->addMessages($messages, $fieldName);

        return $this;
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }

    /**
     * Returns any additional data sent with the result.
     *
     * @return    mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns a list of messages for this result.
     *
     * @return    DResultMessages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Retrieves the title for this result messsage.
     *
     * This is usually constructed from the <code>object</code> and <code>action</code>
     * parameters of the constructor, however can be overriden using the
     * {@link DResult::setTitle()} method.
     *
     * @return    DLabel
     */
    public function getTitle()
    {
        if ($this->title) {
            $title = $this->title;
        } else {
            $title = new DLabel(
                self::class,
                $this->type,
                array(
                    'subject' => (string)$this->subject,
                    'action'  => (string)$this->action,
                )
            );
        }

        return $title;
    }

    /**
     * Returns the result type.
     *
     * @return    string    One of:
     *                    - {@link DResult::TYPE_SUCCESS}
     *                    - {@link DResult::TYPE_WARNING}
     *                    - {@link DResult::TYPE_ERROR}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Determines if any messages have been added to this result.
     *
     * @return    bool
     */
    public function hasMessages()
    {
        return (count($this->messages) > 0);
    }

    /**
     * Determines whether this result indicates that an action
     * was successfully completed.
     *
     * @return    bool
     */
    public function isSuccessful()
    {
        return ($this->type !== self::TYPE_ERROR);
    }

    /**
     * Returns a stdClass object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        $jsonObject = parent::jsonSerialize();
        $jsonObject->success = $this->isSuccessful();

        return $jsonObject;
    }

    /**
     * Merges this result object with another result object.
     *
     * If both results are unsuccessful or both result are successful, the
     * messages of the provided result will be merged into this result.
     *
     * If one result is unsuccessful, the unsuccessful result will override.
     *
     * @param    DResult $result The result to merge with.
     *
     * @return    static
     */
    public function merge(DResult $result = null)
    {
        // Accept null as a parameter but ignore.
        if ($result !== null) {
            // Use provided object and action if none have
            // been specified for this Result.
            if (!$this->subject) {
                $this->subject = $result->subject;
            }
            if (!$this->action) {
                $this->action = $result->action;
            }
            // Merge messages if both results have the same success status.
            if ($this->isSuccessful() === $result->isSuccessful()) {
                $this->messages->addMessages($result->getMessages()->toArray());
                // If this result is successful and the provided
                // result is not, override this result.
            } else {
                if (!$result->isSuccessful()) {
                    $this->type = $result->type;
                    $this->messages = $result->messages;
                } else {
                    // Don't merge any messages.
                }
            }
        }

        return $this;
    }

    /**
     * Normalises deprecated result type options.
     *
     * @param    mixed $type The value to normalise.
     *
     * @return    string    one of:
     *                    - {@link DResult::TYPE_SUCCESS}
     *                    - {@link DResult::TYPE_WARNING}
     *                    - {@link DResult::TYPE_ERROR}
     * @throws    DInvalidParameterValueException    If the provided type is invalid.
     */
    protected function normaliseType($type)
    {
        if ($type === true) {
            $normalised = self::TYPE_SUCCESS;
            // Handle false and null
        } else {
            if (!$type) {
                $normalised = self::TYPE_ERROR;
            } else {
                $normalised = $type;
            }
        }
        if (!isset(self::$types[ $normalised ])) {
            throw new DInvalidParameterValueException(
                'type',
                array(__CLASS__, __FUNCTION__),
                'One of <code>app\\decibel\\utility\\DResult::TYPE_SUCCESS</code>, <code>app\\decibel\\utility\\DResult::TYPE_WARNING</code> or <code>app\\decibel\\utility\\DResult::TYPE_ERROR</code>'
            );
        }

        return $normalised;
    }

    /**
     * Sets additional data to be sent with the result.
     *
     * @param    mixed $data
     *
     * @return    static
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the type parameter of the result to indicate the success of an action.
     *
     * @param    int    $type         Whether or not the operation was successful.
     *                                This should be one of:
     *                                - {@link DResult::TYPE_SUCCESS}
     *                                - {@link DResult::TYPE_WARNING}
     *                                - {@link DResult::TYPE_ERROR}
     *                                Provision of a boolean value is deprecated,
     *                                however, <code>true</code> will set the
     *                                result type to {@link DResult::TYPE_SUCCESS},
     *                                while <code>false</code> will set the result
     *                                type to {@link DResult::TYPE_ERROR}.
     * @param    mixed  $messages     A string, {@link app::decibel::regional::DLabel DLabel}
     *                                instance or an array of strings or
     *                                {@link app::decibel::regional::DLabel DLabel}
     *                                instances.
     * @param    string $field        If specified, the field this error message applies to.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided type is invalid.
     * @throws    DInvalidParameterValueException    If an invalid message value is provided.
     */
    public function setSuccess($type, $messages = array(), $field = null)
    {
        // Handle deprecated boolean value.
        $type = $this->normaliseType($type);
        // Clear any existing messages from a successful result.
        if ($this->type !== self::TYPE_ERROR
            && $type === self::TYPE_ERROR
        ) {
            $this->messages->clearMessages();
        }
        // Assign the type.
        $this->type = $type;
        if (is_array($messages)) {
            $this->addMessages($messages, $field);
        } else {
            $this->addMessage($messages, $field);
        }

        return $this;
    }

    /**
     * Overrides the default title message for this result.
     *
     * @param    string|DLabel $title The title message for the result.
     *
     * @return    void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
