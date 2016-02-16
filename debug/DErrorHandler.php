<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DDebug;
use app\decibel\debug\DDeprecatedException;
use app\decibel\debug\DError;
use app\decibel\debug\DOnErrorHandled;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\http\request\DCliRequest;
use app\decibel\http\request\DRequest;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DSession;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;
use ErrorException;
use Exception;

/**
 * Provides general error handling functions for %Decibel.
 *
 * See the @ref debugging Developer Guide for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging
 */
class DErrorHandler implements DDispatchable, DSingleton
{
    use DBaseClass;
    use DSingletonClass;
    use DEventDispatcher;

    /**
     * The maximum number of debugs and errors that can be stored in the session.
     *
     * Once the limit is reached, older debugs will be shifted off
     * to accomodate new debugs.
     *
     * @var        int
     */
    const DEBUG_SESSION_LIMIT = 50;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::debug::DOnErrorHandled DOnErrorHandled}
     * event.
     *
     * @var        string
     */
    const ON_ERROR_HANDLED = DOnErrorHandled::class;

    /**
     * Determines whether errors should be dumped to standard output
     * once the script has finished executing.
     *
     * @var        bool
     */
    private static $dumpErrors = true;

    /**
     * The number of unreported errors that have occured.
     *
     * @var        int
     */
    public static $errorCount = 0;

    /**
     * The number of unreported exceptions that have occured.
     *
     * @var        int
     */
    public static $exceptionCount = 0;

    /**
     * Debugging information.
     *
     * @var        array
     */
    public static $debugging = array();

    /**
     * Profiling information.
     *
     * @var        array
     */
    public static $profiling = array();

    /**
     * Whether the error handler is running in silent mode.
     *
     * @var        bool
     */
    private static $silent = false;

    /**
     * Creates a new DErrorHandler object.
     *
     * @return    void
     */
    protected function __construct()
    {
        // Determine error handling parameters based on mode.
        if (DApplicationMode::isProductionMode()) {
            $displayErrors = false;
            $errorTypes = E_ALL & ~E_DEPRECATED;
        } else {
            $displayErrors = true;
            $errorTypes = E_ALL | E_STRICT;
        }
        // Set PHP error handling options.
        assert_options(ASSERT_ACTIVE, $displayErrors);
        assert_options(ASSERT_WARNING, 0);
        assert_options(ASSERT_QUIET_EVAL, 0);
        assert_options(ASSERT_CALLBACK, array(self::class, 'assertionHandler'));
        ini_set('error_reporting', $errorTypes);
        ini_set('display_errors', $displayErrors);
        // Disable dumping of errors in production mode.
        self::$dumpErrors = $displayErrors;
        self::prepareDebuggingInformation();
        // Set this class as the exception and error handlers after everything
        // else has completed. Any exceptions thrown during this constructor
        // cannot be handled by itself, as attempting to do so will cause a
        // recursion exception to be thrown during loading of the singleton.
        // Therefore, this is the last thing that should be done.
        set_exception_handler(array(self::class, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'), E_ALL | E_STRICT);
    }

    /**
     * Prepares the internal debugging information arrays.
     *
     * @return    void
     */
    protected static function prepareDebuggingInformation()
    {
        // Setup the debugging array.
        // This is done even is error handling is not enabled, as it is
        // possible to change the application mode during execution.
        $session = DSession::load();
        if (!isset($session[ self::class ])
            && $session->isStarted()
        ) {
            $session[ self::class ] = array(
                'errorCount'     => self::$errorCount,
                'exceptionCount' => self::$exceptionCount,
                'debugging'      => self::$debugging,
                'profiling'      => self::$profiling,
            );
        }
        // Set pointers to debugging information.
        if (self::$errorCount) {
            $session['app\\decibel\\debug\\DErrorHandler-errorCount'] += self::$errorCount;
        }
        self::$errorCount =& $session->get('app\\decibel\\debug\\DErrorHandler-errorCount');
        if (self::$exceptionCount) {
            $session['app\\decibel\\debug\\DErrorHandler-exceptionCount'] += self::$exceptionCount;
        }
        self::$exceptionCount =& $session->get('app\\decibel\\debug\\DErrorHandler-exceptionCount');
        if (self::$debugging) {
            $session['app\\decibel\\debug\\DErrorHandler-debugging'] += self::$debugging;
        }
        self::$debugging =& $session->get('app\\decibel\\debug\\DErrorHandler-debugging');
        if (self::$profiling) {
            $session['app\\decibel\\debug\\DErrorHandler-profiling'] += self::$profiling;
        }
        self::$profiling =& $session->get('app\\decibel\\debug\\DErrorHandler-profiling');
    }

    /**
     * Controls whether errors will be dumped to standard output once the
     * script finished executing.
     *
     * @param    bool $allow      Whether errors can be dumped. If not provided,
     *                            the current setting will be returned without
     *                            being changed.
     *
     * @return    bool    The new value for the allowDump option.
     */
    public static function allowDump($allow = null)
    {
        if (is_bool($allow)) {
            self::$dumpErrors = $allow;
        }

        return self::$dumpErrors;
    }

    /**
     * Handles an application assertion error.
     *
     * @note
     * This is registered as the PHP assertion handler
     * and should not be called directly.
     *
     * @param    string $file File in which the assertion failed.
     * @param    int    $line Line number on which the assertion failed.
     * @param    string $code Code that caused the assertion to fail.
     *
     * @return    void
     */
    public static function assertionHandler($file, $line, $code)
    {
        if (!DApplicationMode::isDebugMode()) {
            return;
        }
        // Create error information object.
        $error = new DError(DError::TYPE_ASSERTION);
        $error->setFile($file);
        $error->setLine($line);
        $error->setMessage("Assertion failed: <code>{$code}</code>");
        $error->setBacktrace((string)DBacktrace::create());
        // Handle the standardised error message.
        $errorHandler = self::load();
        $errorHandler->handle($error);
    }

    /**
     * Clears all debugging information.
     *
     * @return    void
     */
    public static function clearDebugging()
    {
        self::$errorCount = 0;
        self::$exceptionCount = 0;
        self::$debugging = array();
        self::$profiling = array();
    }

    /**
     * Debugs a variable.
     *
     * @param    mixed $variable              The variable to debug.
     * @param    bool  $includeBacktrace      If set to true, a backtrace will be
     *                                        included. Defaults to false.
     * @param    bool  $returnAsString        If set to true, the debug information
     *                                        will be returned by the function
     *                                        as a string.
     *
     * @return    void
     */
    public static function debug($variable, $includeBacktrace = false, $returnAsString = false)
    {
        if (DApplicationMode::isProductionMode()) {
            return;
        }
        $debug = new DDebug($variable, $includeBacktrace, !$returnAsString);
        // Return as a string.
        if ($returnAsString) {
            return (string)$debug;
        }
        // Output to standard error for debug.
        if (DRequest::load() instanceof DCliRequest) {
            fwrite(STDERR, "{$debug}\n\n");
        } else {
            if (count(self::$debugging) <= self::DEBUG_SESSION_LIMIT) {
                self::$debugging[] = $debug;
            } else {
                // If the limit on debugging for this request/session has been reached,
                // don't do anything with the debug, otherwise we may exceed the memory limit.
            }
        }
    }

    /**
     * Handles errors generated during application execution.
     *
     * @note
     * This is registered as the PHP error handler
     * and should not be called directly.
     *
     * @param    int    $errorType The error type.
     * @param    string $errorMsg  The error message.
     * @param    string $errorFile The file in which the error occured.
     * @param    int    $errorLine The line on which the error occured.
     *
     * @throws DRecursionException
     * @throws ErrorException
     */
    public static function errorHandler($errorType, $errorMsg,
                                        $errorFile, $errorLine)
    {
        // Error message was suppressed with @ operator, so ignore it.
        if (error_reporting() === 0
            // Silent mode enabled.
            || self::$silent
        ) {
            return;
        }

		if (DApplicationMode::isProductionMode()) {
           // Create error information object.
            $error = DError::createFromPhpError(
                $errorType,
                $errorMsg,
                $errorFile,
                $errorLine
            );
            // Handle the standardised error message.
            $errorHandler = self::load();
            $errorHandler->handle($error);

        // Always throw an exception when developing/debugging.
		} else {
			throw new ErrorException($errorMsg, 0, $errorType, $errorFile, $errorLine);
 		}
    }

    /**
     * Handles exceptions generated during application execution.
     *
     * @note
     * This is registered as the PHP exception handler
     * and should not be called directly.
     *
     * @warning
     * This function will end script execution.
     *
     * @param    Exception $exception The thrown exception.
     *
     * @return    void
     */
    public static function exceptionHandler(Exception $exception)
    {
        $error = DError::createFromException($exception);
        $errorHandler = self::load();
        $errorHandler->handle($error);
        $request = DRequest::load();
        if ($request instanceof DCliRequest) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        // Dump errors is called as sometimes the shut down function is not.
        self::$exceptionCount++;
        self::dumpErrors();
        exit(1);
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return self::ON_ERROR_HANDLED;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_ERROR_HANDLED,
        );
    }

    /**
     * Returns the last handled error.
     *
     * @return    DError
     */
    public static function getLastError()
    {
        return array_pop(self::$debugging);
    }

    /**
     * Handles standardised error messages.
     *
     * @param    DError $error Error information.
     *
     * @return    void
     */
    private function handle(DError $error)
    {
        // Log the error for display if not in production mode.
        if (!DApplicationMode::isProductionMode()) {
            $request = DRequest::load();
            if ($request instanceof DCliRequest) {
                if (defined('DECIBEL_TESTING')) {
                    self::$debugging[] = $error;
                }
                if ($error->getType() !== DError::TYPE_HANDLED_EXCEPTION) {
                    fwrite(STDERR, "{$error}\n\n");
                }
            } else {
                self::$debugging[] = $error;
                self::$errorCount++;
            }
        }
        // Trigger the handle error event to allow other functions to manage errors.
        // Don't let any exception be thrown here.
        try {
            $event = new DOnErrorHandled();
            $event->setError($error);
            $this->notifyObservers($event);
        } catch (Exception $e) {
            if (DApplicationMode::isDebugMode()) {
                echo DError::createFromException($e);
            }
        }
    }

    /**
     * Notifies the developer that a deprecated feature has been used
     * by logging a {@link DDeprecatedException}.
     *
     * @warning
     * The exception will halt execution when the application is in debug mode.
     *
     * @note
     * This method will not do anything in test or production modes.
     *
     * @param    string $deprecated  Deprecated feature.
     * @param    string $replacement Feature that should be used instead.
     *
     * @return    void
     * @throws    DDeprecatedException
     */
    public static function notifyDeprecation($deprecated, $replacement)
    {
        if (!DApplicationMode::isDebugMode()) {
            return;
        }
        throw new DDeprecatedException($deprecated, $replacement);
    }

    /**
     * Allows an exception to be logged without being thrown.
     *
     * @param    Exception $exception The exception.
     *
     * @return    void
     */
    public static function logException(Exception $exception)
    {
        // Otherwise log the exception and continue.
        $error = DError::createFromException($exception);
        $error->setType(DError::TYPE_HANDLED_EXCEPTION);
        $errorHandler = self::load();
        $errorHandler->handle($error);
    }

    /**
     * Enables or disabled silent mode.
     *
     * In silent mode no errors will be reported, equivalent to the '@' prefix
     * applied to PHP functions.
     *
     * @param    bool $enabled Whether silent mode should be enabled or disabled.
     *
     * @return    void
     */
    public static function silentMode($enabled)
    {
        self::$silent = $enabled;
    }

    /**
     * Allows an exception to be thrown only in debug mode.
     *
     * @param    Exception $exception     The exception.
     * @param    bool      $log           Whether to log the exception if the
     *                                    application is running in a mode other
     *                                    than debug mode.
     *
     * @return    void
     */
    public static function throwException(Exception $exception, $log = true)
    {
        // In debug mode, throw the exception.
        if (DApplicationMode::isDebugMode()) {
            throw $exception;
        }
        // Otherwise log the exception and continue.
        if ($log) {
            DErrorHandler::logException($exception);
        }
    }

    /**
     * Dumps errors and debugging information on PHP shutdown if the page could not
     * be successfully displayed. This will only occur if the application is running
     * in debug or test mode.
     *
     * @note
     * This is registered as a shutdown function and should
     * not be called directly.
     *
     * @return    void
     */
    public static function dumpErrors()
    {
        // Check if errors can be dumped.
        if (!self::$dumpErrors
            // Not in debug mode.
            || DApplicationMode::isProductionMode()
            // Nothing to debug.
            || (count(self::$debugging) + count(self::$profiling)) === 0
        ) {
            return;
        }
        // Don't do anything if only non-fatal errors occured.
        // In this case they will be reported through the debug console.
        $lastError = error_get_last();
        if (($lastError === null || $lastError['type'] !== E_ERROR)
            && self::$exceptionCount == 0
        ) {
            return;
        }
        echo '<h1>Decibel Debug Console</h1>';
        // Debugs and errors.
        echo implode(self::$debugging);
        // Page Statistics.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            echo implode(self::$profiling);
        }
        // Remove reported debugging information.
        self::clearDebugging();
    }

    /**
     * Ensures a 500 status code is returned to the client should a fatal
     * error occur during execution.
     *
     * @note
     * This is registered as a shutdown function.
     *
     * @return    void
     */
    public static function send500()
    {
        // Check if a fatal error occurred.
        $lastError = error_get_last();
        if ($lastError === null) {
            return;
        }
        if ($lastError['type'] === E_ERROR
            || $lastError['type'] === E_PARSE
            || $lastError['type'] === E_COMPILE_ERROR
        ) {
            header('HTTP/1.1 500 Internal Server Error');
        }
    }
}
