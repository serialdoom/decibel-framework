<?php

use app\decibel\debug\DErrorHandler;
use app\decibel\utility\DStr as Str;

/**
 * Debugs a variable.
 *
 * Debugged variables will only be shown if the Application Debug Mode is enabled.
 *
 * @param    mixed $variable              The variable to debug.
 * @param    bool  $includeBacktrace      If set to true, a backtrace will be
 *                                        included. Defaults to false.
 *
 * @return    void
 */
function debug($variable, $includeBacktrace = false)
{
    DErrorHandler::debug($variable, $includeBacktrace);
}

/**
 * Sets a variable to a default value if it has not already been
 * defined.
 *
 * This essentially provides short-hand for the following code:
 *
 * <code>
 * if (!isset($var)) {
 *    $var = 'value';
 * }
 * </code>
 *
 * @param    mixed $var     Pointer to the variable to set.
 * @param    mixed $default The default value.
 *
 * @return    void
 */
function set_default(&$var, $default)
{
    if (!isset($var)) {
        $var = $default;
    }
}

/**
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return value($default);
    }

    if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
        return substr($value, 1, -1);
    }

    return $value;
}

/**
 * Function that either returns the original value or executes it if
 * it contains a Closure.
 *
 * @param mixed $value
 *
 * @return mixed
 */
function value($value)
{
    return ($value instanceof Closure) ? call_user_func($value) : $value;
}
