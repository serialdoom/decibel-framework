<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DExceptionReflection;
use app\decibel\http\error\DInternalServerError;
use app\decibel\reflection\DReflectable;
use app\decibel\reflection\DReflectionClass;
use app\decibel\regional\DLabel;
use app\decibel\regional\DLabelRepository;
use app\decibel\regional\DRegionalException;
use app\decibel\regional\DUnknownLabelException;
use app\decibel\stream\DTextStream;
use Exception;
use JsonSerializable;
use stdClass;

/**
 * Base class for all %Decibel exceptions.
 *
 * See @ref debugging_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging
 */
abstract class DException extends Exception
    implements JsonSerializable, DReflectable, DDebuggable
{
    /**
     * 'Message' label name.
     *
     * @var        string
     */
    const LABEL_MESSAGE = 'message';

    /**
     * Backtrace for this exception.
     *
     * @var        DBacktrace
     */
    protected $backtrace;

    /**
     * The original information provided to the constructor.
     *
     * @var        array
     */
    protected $information;

    /**
     * Creates a new DException.
     *
     * @param    array $information       Additional information to be incorporated
     *                                    into the exception message.
     *
     * @return  static
     */
    public function __construct(array $information = array())
    {
        $this->information = $information;
        $message = $this->generateMessage($information);
        // Generate a backtrace.
        $this->backtrace = DBacktrace::create();
        parent::__construct($message);
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'file'        => $this->file,
            'line'        => $this->line,
            'message'     => $this->message,
            'information' => $this->information,
            'backtrace'   => $this->backtrace,
        );
    }

    /**
     * Appends the provided text to the end of the exception's message.
     *
     * @param    string $text The text to append.
     *
     * @return    void
     */
    public function appendToMessage($text)
    {
        $this->message .= $text;
    }

    /**
     * Formats a <code>callable</code> value as a string,
     * for use in exception messages.
     *
     * @param    callable $callable The callable value.
     *
     * @return    string
     */
    public function formatCallable($callable)
    {
        if (is_array($callable)) {
            if (isset($callable[0])
                && isset($callable[1])
            ) {
                $formatted = "{$callable[0]}::{$callable[1]}()";
            } else {
                $formatted = 'unknown';
            }
        } else {
            $formatted = (string)$callable;
        }

        return $formatted;
    }

    /**
     * Generates the exception message, using the provided variables.
     *
     * @param    array $variables
     *
     * @return    DLabel
     * @throws    DInternalServerError    If no message has been defined for the exception
     *                                    of variable for the message are missing.
     */
    protected function generateMessage(array $variables)
    {
        // @todo This is stopping exception messages from showing correctly,
        // probably due to changes in the loading of translations following
        // introduction of the registry. If commented out, messages display
        // correctly, however a recursion exception can appear if exception
        // is thrown too early in the bootstrap.
        if (!DLabelRepository::isLoaded()) {
            return var_export($variables, true);
        }
        try {

            // No message has been defined for this exception,
            // or variables are missing.
        } catch (DRegionalException $exception) {
            $error = new DInternalServerError();
            $body = new DTextStream(
                DError::createFromException($exception)
            );
            $error->setBody($body);
            throw $error;
        }
    }

    /**
     * Returns a human-readable description for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        try {
            $description = new DLabel(get_called_class(), self::LABEL_MESSAGE);
        } catch (DUnknownLabelException $exception) {
            $description = null;
        }

        return $description;
    }

    /**
     * Returns a human-readable name for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return get_called_class();
    }

    /**
     * Provides a reflection of this class.
     *
     * @return    DReflectionClass
     */
    public static function getReflection()
    {
        return new DExceptionReflection(get_called_class());
    }

    /**
     * Gets the stack trace for this exception.
     *
     * @note
     * This should be used instead of <code>Exception::getTrace()</code>
     * as a better stack traces will be provided.
     *
     * @return    DBacktrace
     */
    public function getBackTrace()
    {
        return $this->backtrace;
    }

    /**
     * Specifies whether it is possible for the application to recover from
     * this type of exception and continue execution.
     *
     * @return    bool
     */
    public function isRecoverable()
    {
        return true;
    }

    /**
     * Returns a stdClass object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        $jsonObject = new stdClass();
        // Determine which properties to show - only show the message if
        // we are not in debug mode.
        $jsonObject->qualifiedName = get_class($this);
        $jsonObject->message = $this->message;
        if (DApplicationMode::isDebugMode()) {
            $jsonObject->file = $this->file;
            $jsonObject->line = $this->line;
            $jsonObject->backtrace = $this->backtrace->jsonPrepare();
        }

        return $jsonObject;
    }

    /**
     * Regenerates the exception backtrace so that the exception appears to
     * have been thrown from the location that this function is called.
     *
     * @return    static
     */
    public function regenerateBacktrace()
    {
        $this->backtrace = DBacktrace::create();

        return $this;
    }
}
