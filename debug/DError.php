<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\http\request\DRequest;
use app\decibel\http\request\DCliRequest;
use app\decibel\model\debug\DInvalidFieldValueException;
use Exception;

/**
 * Stores information about an error that has occured within Decibel.
 *
 * @author        Timothy de Paris
 */
class DError extends DDebuggingInformation
{
    /**
     * Denotes an assertion error occured.
     *
     * @var        int
     */
    const TYPE_ASSERTION = 1;

    /**
     * Denotes a notice error occured.
     *
     * @var        int
     */
    const TYPE_NOTICE = 2;

    /**
     * Denotes a warning error occured.
     *
     * @var        int
     */
    const TYPE_WARNING = 3;

    /**
     * Denotes an error occured.
     *
     * @var        int
     */
    const TYPE_ERROR = 4;

    /**
     * Denotes an unhandled exception occured (execution ceased).
     *
     * @var        int
     */
    const TYPE_EXCEPTION = 5;

    /**
     * Denotes deprecated code was called.
     *
     * @var        int
     */
    const TYPE_DEPRECATED = 6;

    /**
     * Denotes code does not adhere to strict coding standards.
     *
     * @var        int
     */
    const TYPE_STRICT = 7;

    /**
     * Denotes a fatal error.
     *
     * @var        int
     */
    const TYPE_FATAL = 8;

    /**
     * Denotes a handled exception occured (exceution continued).
     *
     * @var        int
     */
    const TYPE_HANDLED_EXCEPTION = 9;

    /**
     * Mapping of PHP to Decibel error codes.
     *
     * @var        array
     */
    private static $phpErrorTypes = array(
        E_STRICT            => self::TYPE_STRICT,
        E_DEPRECATED        => self::TYPE_DEPRECATED,
        E_WARNING           => self::TYPE_WARNING,
        E_USER_WARNING      => self::TYPE_WARNING,
        E_NOTICE            => self::TYPE_NOTICE,
        E_USER_NOTICE       => self::TYPE_NOTICE,
        E_ERROR             => self::TYPE_ERROR,
        E_USER_ERROR        => self::TYPE_ERROR,
        E_RECOVERABLE_ERROR => self::TYPE_ERROR,
        E_PARSE             => self::TYPE_ERROR,
    );

    /**
     * Available error types.
     *
     * @var        array
     */
    private static $types = array(
        self::TYPE_ASSERTION         => 'Assertion',
        self::TYPE_NOTICE            => 'Notice',
        self::TYPE_WARNING           => 'Warning',
        self::TYPE_ERROR             => 'Error',
        self::TYPE_EXCEPTION         => 'Unhandled Exception',
        self::TYPE_HANDLED_EXCEPTION => 'Handled Exception',
        self::TYPE_DEPRECATED        => 'Deprecated',
        self::TYPE_STRICT            => 'Strict Standards',
        self::TYPE_FATAL             => 'Fatal',
    );

    /**
     * Type of error.
     *
     * @var        string
     */
    protected $type;

    /**
     * Creates a new {@link DError} object.
     *
     * @param    int $type The error type.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If a provided value is invalid.
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * Returns the error information formatted with HTML.
     *
     * @return    string
     */
    public function __toString()
    {
        $type = $this->getTypeName();
        $file = $this->getFile();
        $line = $this->getLine();
        $msg = $this->getMessage();
        $trace = $this->getBacktrace();
        $request = DRequest::load();
        if ($request instanceof DCliRequest) {
            $stringValue = "{$type} ({$file}, line {$line})\n{$msg}\n{$trace}";
        } else {
            $stringValue = "<p><strong>{$type} ({$file}, line {$line})</strong>"
                . "<blockquote>{$msg}<br /><pre>{$trace}</pre></blockquote></p>";
        }

        return $stringValue;
    }

    /**
     * Creates a DError object from an exception.
     *
     * @param    Exception $exception The exception.
     *
     * @return    DError
     */
    public static function createFromException(Exception $exception)
    {
        $error = new DError(DError::TYPE_EXCEPTION);
        $error->setMessage(get_class($exception) . ': ' . $exception->getMessage());
        if ($exception instanceof DException) {
            $backtrace = $exception->getBackTrace();
            $stackFrame = $backtrace->getStackFrame(0);
            $error->setFile($stackFrame->getFile());
            $error->setLine($stackFrame->getLine());
            $error->setBacktrace((string)$backtrace);
        } else {
            $error->setFile($exception->getFile());
            $error->setLine($exception->getLine());
            $error->setBacktrace($exception->getTraceAsString());
        }

        return $error;
    }

    /**
     * Creates a {@link DError} object from standard PHP error information.
     *
     * @param    int    $errorType The error type.
     * @param    string $errorMsg  The error message.
     * @param    string $errorFile The file in which the error occured.
     * @param    int    $errorLine The line on which the error occured.
     *
     * @return    DError
     */
    public static function createFromPhpError($errorType, $errorMsg, $errorFile, $errorLine)
    {
        $error = new DError(self::$phpErrorTypes[ $errorType ]);
        $error->setMessage($errorMsg);
        $error->setFile($errorFile);
        $error->setLine($errorLine);
        $error->setBacktrace(DBacktrace::create());

        return $error;
    }

    /**
     * Returns a list of available error types.
     *
     * @return    array
     */
    public static function getErrorTypes()
    {
        return self::$types;
    }

    /**
     * Returns the type of error.
     *
     * @return    int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the type of error as a string.
     *
     * @return    string
     */
    public function getTypeName()
    {
        return self::$types[ $this->type ];
    }

    /**
     * Determines if this error represents an exception.
     *
     * This include errors with a type of {@link DError::TYPE_EXCEPTION}
     * or {@link DError::TYPE_HANDLED_EXCEPTION}.
     *
     * @return    bool
     */
    public function isException()
    {
        return ($this->type === DError::TYPE_EXCEPTION
            || $this->type === DError::TYPE_HANDLED_EXCEPTION);
    }

    /**
     * Sets the type of error.
     *
     * @param    int $type        The error type. One of:
     *                            - {@link DError::TYPE_ASSERTION}
     *                            - {@link DError::TYPE_NOTICE}
     *                            - {@link DError::TYPE_WARNING}
     *                            - {@link DError::TYPE_ERROR}
     *                            - {@link DError::TYPE_EXCEPTION}
     *                            - {@link DError::TYPE_HANDLED_EXCEPTION}
     *                            - {@link DError::TYPE_DEPRECATED}
     *                            - {@link DError::TYPE_STRICT}
     *                            - {@link DError::TYPE_FATAL}
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If an invalid value is provided.
     */
    public function setType($type)
    {
        if (!isset(self::$types[ $type ])) {
            throw new DInvalidParameterValueException(
                'type',
                array(__CLASS__, __FUNCTION__),
                'Valid error type.'
            );
        }
        $this->type = $type;

        return $this;
    }
}
