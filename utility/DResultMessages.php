<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\regional\DLabel;

/**
 * Holds a list of messages associated with a {@link DResult}.
 *
 * @author        Timothy de Paris
 */
class DResultMessages extends DList
{
    /**
     * Contains hashes of the messages, used to check for existing messages.
     *
     * @var array
     */
    protected $hashes;

    /**
     * Creates a new {@link DResultMessages}.
     *
     * @return    static
     */
    public function __construct()
    {
        parent::__construct();
        $this->hashes = array();
    }

    /**
     * Adds a message to this result's list of messages only if in debug mode.
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
    public function addDebugMessage($message, $fieldName = null)
    {
        if (DApplicationMode::isDebugMode()) {
            $this->addMessage($message, $fieldName);
        }

        return $this;
    }

    /**
     * Adds a single message or an array of messages to this result's list of messages
     * only if in debug mode.
     *
     * @param    mixed  $messages     A string, {@link app::decibel::regional::DLabel DLabel}
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
    public function addDebugMessages($messages, $fieldName = null)
    {
        if (DApplicationMode::isDebugMode()) {
            $this->addMessages($messages, $fieldName);
        }

        return $this;
    }

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
        // Validate provided message.
        $this->validateMessage($message);
        // Ignore repeated messages.
        if (!$this->hasMessage($message, $fieldName)) {
            if ($fieldName == null) {
                $fieldName = sizeof($this->values);
            }
            $this->values[ $fieldName ] = $message;
            $this->hashes[ $fieldName ] = md5((string)$message);
        }

        return $this;
    }

    /**
     * Adds a single message or an array of messages to this result's list of messages.
     *
     * @param    array  $messages     A string, {@link app::decibel::regional::DLabel DLabel}
     *                                instance or an array of strings or
     *                                {@link app::decibel::regional::DLabel DLabel}
     *                                instances.
     * @param    string $fieldName    If provided, any messages in the provided array that do not
     *                                have a associative string keys will be assigned to this field.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If an invalid message value is provided.
     */
    public function addMessages(array $messages, $fieldName = null)
    {
        // Array of messages.
        foreach ($messages as $fieldN => $message) {
            // Determine key to use for this message.
            if (is_numeric($fieldN)) {
                $key = $fieldName;
            } else {
                $key = $fieldN;
            }
            try {
                $this->addMessage($message, $key);
            } catch (DInvalidParameterValueException $exception) {
                $debug = $exception->generateDebug();
                throw new DInvalidParameterValueException(
                    'messages',
                    array(__CLASS__, __FUNCTION__),
                    'List containing ' . $debug['information']['expected']
                );
            }
        }

        return $this;
    }

    /**
     * Changes the name of a field in this result's messages.
     *
     * @param    string $oldFieldName The field name to change.
     * @param    string $newFieldName The new field name.
     *
     * @return    bool    <code>true</code> if the <code>$oldFieldName</code> was
     *                    present and changed, <code>false</code> if not.
     */
    public function changeFieldName($oldFieldName, $newFieldName)
    {
        if (isset($this->values[ $oldFieldName ])) {
            $message = $this->values[ $oldFieldName ];
            unset($this->values[ $oldFieldName ]);
            $this->values[ $newFieldName ] = $message;
            $changed = true;
        } else {
            $changed = false;
        }

        return $changed;
    }

    /**
     * Clears all the messages from this list.
     *
     * @return    static
     */
    public function clearMessages()
    {
        $this->values = array();
        $this->hashes = array();

        return $this;
    }

    /**
     * Determines if this result has a message matching the provided paramters.
     *
     * @param    mixed  $message
     * @param    string $fieldName
     *
     * @return    bool
     */
    public function hasMessage($message, $fieldName = null)
    {
        if ($fieldName === null) {
            $messageHash = md5((string)$message);
            $hasMessage = in_array($messageHash, $this->hashes);
        } else {
            $hasMessage = isset($this->values[ $fieldName ]);
        }

        return $hasMessage;
    }

    /**
     * Validates a message value.
     *
     * @param    mixed $message The message to validate.
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If the message is not valid.
     */
    protected function validateMessage($message)
    {
        if (!is_string($message)
            && !is_object($message)
            && !$message instanceof DLabel
        ) {
            throw new DInvalidParameterValueException(
                'message',
                array(__CLASS__, 'addMessage'),
                'string or <code>app\decibel\regional\DLabel</code> object'
            );
        }
    }
}
