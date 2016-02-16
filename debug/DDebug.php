<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\debug;

use app\decibel\http\request\DRequest;
use app\decibel\http\request\DCliRequest;
use app\decibel\model\DModel;

/**
 * Stores information about a debugged variable.
 *
 * @author        Timothy de Paris
 */
class DDebug extends DDebuggingInformation
{
    /**
     * Creates a new DDebug object.
     *
     * @param    mixed $variable              The variable to debug.
     * @param    bool  $includeBacktrace      If set to true, a backtrace will be
     *                                        included. Defaults to false.
     * @param    bool  $includeLocation       If set to false, the location of the
     *                                        debug will not be included.
     *                                        Defaults to true.
     * @param    int   $locationDepth         The depth within the backtrace that
     *                                        the debug location will be taken from.
     *
     * @return    static
     */
    public function __construct($variable, $includeBacktrace = false,
                                $includeLocation = true, $locationDepth = 0)
    {
        // Check if HTML in strings should be encoded.
        $htmlEntities = !DRequest::load() instanceof DCliRequest;
        // Convert debuggable objects.
        $variable = $this->prepareForDebug($variable);
        $this->setMessage(
            $this->variableToMsg($variable, $htmlEntities)
        );
        if ($includeLocation || $includeBacktrace) {
            $backtrace = DBacktrace::create();
        }
        // Determine where debug was called.
        if ($includeLocation) {
            $stackFrame = $backtrace->getStackFrame($locationDepth);
            $this->setFile($stackFrame->getFile());
            $this->setLine($stackFrame->getLine());
        }
        // Generate trace string.
        if ($includeBacktrace) {
            $this->setBacktrace($backtrace);
        }
    }

    /**
     * Returns the debug information formatted with HTML.
     *
     * @return    string
     */
    public function __toString()
    {
        $file = $this->getFile();
        $line = $this->getLine();
        $msg = $this->getMessage();
        $trace = $this->getBacktrace();
        if (!$file) {
            return $msg;
        }
        $request = DRequest::load();
        if ($request instanceof DCliRequest) {
            $trace = strip_tags($trace);
            $stringValue = "\nDebug ({$file}, line {$line})\n{$msg}\n\n{$trace}";
        } else {
            if ($trace) {
                $msg .= "<br /><pre>{$trace}</pre>";
            }
            $stringValue = "<p class=\"app-decibel-debug-ddebug\"><strong>Debug ({$file}, line {$line})</strong><blockquote>{$msg}</blockquote></p>";
        }

        return $stringValue;
    }

    /**
     * Converts an array to a debuggable message.
     *
     * @param    array $variable     The variable to convert.
     * @param    bool  $htmlEntities Whether to encode HTML entities.
     *
     * @return    string
     */
    protected function arrayToMsg(array &$variable, $htmlEntities = false)
    {
        if (!$htmlEntities) {
            $msg = var_export($variable, true);
        } else {
            // If there is a qualified name in the array,
            // it is a representation of a debuggable.
            if (isset($variable[ '_' . DModel::FIELD_QUALIFIED_NAME ])) {
                $qualifiedName = 'object(' . $variable[ '_' . DModel::FIELD_QUALIFIED_NAME ] . ')';
                unset($variable[ '_' . DModel::FIELD_QUALIFIED_NAME ]);
            } else {
                if (isset($variable[ DModel::FIELD_QUALIFIED_NAME ])) {
                    $qualifiedName = 'array(' . $variable[ DModel::FIELD_QUALIFIED_NAME ] . ')';
                    unset($variable[ DModel::FIELD_QUALIFIED_NAME ]);
                } else {
                    $qualifiedName = 'array(' . count($variable) . ')';
                }
            }
            // Create a row for each array value.
            $rows = '';
            foreach ($variable as $key => &$value) {
                $rows .= $this->arrayValueToMsg($key, $value);
            }
            $msg = "<table><tr><td colspan=\"2\">{$qualifiedName}</td></tr>{$rows}</table>";
        }

        return $msg;
    }

    /**
     * Converts an array value to a debuggable message.
     *
     * @param    string $key              Pointer to the value's key in the array
     * @param    string $value            The array value to convert. This will
     *                                    already be a pointer before being passed.
     *
     * @return    string    HTML representation of the key and value as an HTML
     *                    table row.
     */
    protected function arrayValueToMsg(&$key, $value)
    {
        if (strpos($key, '*') === 0) {
            $key = substr($key, 1);
            if (strpos($key, '|') !== false) {
                $valueString = $this->formatArrayValue($key, $value);
            } else {
                $valueString = $value;
            }
        } else {
            $tempDebug = new DDebug($value, false, false);
            $valueString = (string)$tempDebug;
        }

        return "<tr><td>{$key} =&gt;</td><td>{$valueString}</td></tr>";
    }

    /**
     * Formats an array value according to the specified class.
     *
     * @param    string $key      The array value key. This will be modified
     *                            to remove the formatting class.
     * @param    string $value    The array value to be formatted.
     *
     * @return    string
     */
    protected function formatArrayValue(&$key, $value)
    {
        list($key, $class) = explode('|', $key);
        if ($class === 'sql') {
            $value = preg_replace(
                array(
                    '/(FROM|LEFT JOIN|RIGHT JOIN|INNER JOIN|JOIN|WHERE|LIMIT|GROUP BY|HAVING|ORDER BY)[ $]/i',
                    '/(AND)([ $])/i',
                ),
                array(
                    "<br />$1 ",
                    "<br />    $1 ",
                ),
                $value
            );
        }

        return "<pre class=\"formatted {$class}\">{$value}</pre>";
    }

    /**
     * Converts a boolean to a debuggable message.
     *
     * @param    bool $variable The variable to convert.
     *
     * @return    string
     */
    protected function booleanToMsg(&$variable)
    {
        if ($variable) {
            $message = 'bool(true)';
        } else {
            $message = 'bool(false)';
        }

        return $message;
    }

    /**
     * Converts an double to a debuggable message.
     *
     * @param    int $variable The variable to convert.
     *
     * @return    string
     */
    protected function doubleToMsg(&$variable)
    {
        return sprintf('float(%s)', $variable);
    }

    /**
     * Converts an integer to a debuggable message.
     *
     * @param    int $variable The variable to convert.
     *
     * @return    string
     */
    protected function integerToMsg(&$variable)
    {
        return sprintf('int(%d)', $variable);
    }

    /**
     * Converts an object to a debuggable message.
     *
     * @param    object $variable     The variable to convert.
     * @param    bool   $htmlEntities Whether to encode HTML entities.
     *
     * @return    string
     */
    protected function objectToMsg(&$variable, $htmlEntities = false)
    {
        if (!$htmlEntities) {
            $message = print_r($variable, true);
        } else {
            $message = str_replace(
                array(' ', "\n"),
                array('&nbsp;', '<br />'),
                htmlentities(print_r($variable, true), ENT_QUOTES)
            );
        }

        return $message;
    }

    /**
     * Converts an object to a debuggable message.
     *
     * @param    object $variable     The variable to convert.
     * @param    bool   $htmlEntities Whether to encode HTML entities.
     *
     * @return    string
     */
    protected function stringToMsg(&$variable, $htmlEntities = false)
    {
        if ($htmlEntities) {
            $message = htmlentities($variable, ENT_QUOTES);
        } else {
            $message = $variable;
        }

        return sprintf("string(%d) '%s'",
                       strlen($variable),
                       $message
        );
    }

    /**
     * Converts a variable to a debuggable message.
     *
     * @param    mixed $variable     The variable to convert.
     * @param    bool  $htmlEntities Whether to encode HTML entities.
     *
     * @return    string
     */
    protected function variableToMsg(&$variable, $htmlEntities = false)
    {
        $type = gettype($variable);
        $methodName = "{$type}ToMsg";
        if (method_exists($this, $methodName)) {
            $message = $this->$methodName($variable, $htmlEntities);
        } else {
            if ($type === 'NULL') {
                $message = 'NULL';
            } else {
                $message = (string)$variable;
            }
        }

        return $message;
    }

    /**
     * Prepares data for debugging.
     *
     * @param    mixed $variable
     *
     * @return    string
     */
    protected function prepareForDebug($variable)
    {
        // Convert debuggable objects.
        if ($variable instanceof DDebuggable) {
            $qualifiedName = get_class($variable);
            $variable = $variable->generateDebug();
            $variable[ '_' . DModel::FIELD_QUALIFIED_NAME ] = $qualifiedName;
        }
        if (is_array($variable)) {
            foreach ($variable as $key => $value) {
                $variable[ $key ] = $this->prepareForDebug($value);
            }
        }

        return $variable;
    }
}
