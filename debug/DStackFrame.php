<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DString;

/**
 * Represents a stack frame in a backtrace.
 *
 * @author    Timothy de Paris
 */
class DStackFrame
{
    use DBaseClass;
    /**
     * Name of the source file.
     *
     * @var        string
     */
    protected $file;
    /**
     * Name of the function.
     *
     * @var        string
     */
    protected $function;
    /**
     * Line in the source file.
     *
     * @var        string
     */
    protected $line;
    /**
     * Function field name.
     *
     * @var        string
     */
    const FIELD_FUNCTION = 'function';

    /**
     * Returns a string representation of the stack frame.
     *
     * @return    string
     */
    public function __toString()
    {
        $function = $this->function;
        $file = $this->file;
        $line = $this->line;
        // Format trace as a string.
        if ($file === null) {
            $formattedLine = "[internal function]: {$function}";
        } else {
            $formattedFile = "{$file}({$line})";
            if (self::isFileEditable($file)) {
                $formattedLine = "{$formattedFile}: {$function}";
            } else {
                $formattedLine = "<u>{$formattedFile}</u>: {$function}";
            }
        }

        return $formattedLine;
    }

    /**
     * Returns a string representation of a function argument.
     *
     * @param    mixed $argument The argument to convert.
     *
     * @return    string
     */
    protected static function argumentToString($argument)
    {
        $type = strtolower(gettype($argument));
        switch ($type) {
            case 'array':
                $stringValue = sprintf('array(%s)', count($argument));
                break;
            case 'object':
                $stringValue = get_class($argument);
                break;
            case 'string':
                $stringValue = static::prepareString($argument);
                break;
            case 'boolean':
                $stringValue = $argument ? 'true' : 'false';
                break;
            case 'NULL':
                $stringValue = 'NULL';
                break;
            case 'integer':
                $stringValue = (string)$argument;
                break;
            default:
                $stringValue = $type;
                break;
        }

        return $stringValue;
    }

    /**
     * Prepares a string argument for use in a stack frame.
     *
     * @param    string $argument
     *
     * @return    string
     */
    protected static function prepareString($argument)
    {
        $search = array('/[\n\r]+/', '/\t/');
        $replace = array('\\n', '\\t');
        $argument = preg_replace($search, $replace, DString::charLimit($argument, 40, '...'));

        return sprintf("'%s'", htmlspecialchars($argument, ENT_QUOTES));
    }

    /**
     * Creates a new {@link DStackFrame}.
     *
     * @param    string $file     File name.
     * @param    int    $line     Line number.
     * @param    string $function Name of the called function.
     * @param    array  $args     Arguments passed to the function.
     * @param    string $class    Class on which the method was called, if applicable.
     * @param    string $type     Method call type ('->' or '::'), if applicable.
     *
     * @return    DStackFrame
     */
    public static function create($file, $line, $function,
                                  array $args = array(), $class = null, $type = null)
    {
        $frame = new DStackFrame();
        // Format file information.
        if ($file !== null) {
            $frame->file = DString::singleQuoteSlashes($file);
            $frame->line = $line;
        }
        // Format function information.
        if ($class !== null) {
            $function = "{$class}{$type}{$function}";
        }
        // Add arguments.
        $formattedArgs = array();
        foreach ($args as $arg) {
            $formattedArgs[] = self::argumentToString($arg);
        }
        $function .= '(' . implode(', ', $formattedArgs) . ')';
        $frame->function = $function;

        return $frame;
    }

    /**
     * Returns the stack frame file.
     *
     * @return    string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the stack frame function.
     *
     * @return    string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Returns the stack frame line.
     *
     * @return    int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Determines if a file is editable by the developer.
     *
     * This requires that the file is not encoded.
     *
     * @param    string $filename Name of the file to test.
     *
     * @return    bool
     */
    protected static function isFileEditable($filename)
    {
        // Ignore Phar files and eval'd code.
        if (preg_match('/(^phar:\/\/|eval\(\))/', $filename)) {
            $editable = false;
            // Check if this is a core file.
        } else {
            $editable = preg_match('/[\\\\\/]app[\\\\\/]decibel(-[a-zA-Z0-9]+)?[\\\\\/]/', $filename);
        }

        return $editable;
    }

    /**
     * Sets the stack frame file.
     *
     * @param    string $file The filename.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If an invalid value is provided.
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Sets the stack frame function.
     *
     * @param    string $function The function name and arguments.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If an invalid value is provided.
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Sets the stack frame line.
     *
     * @param    int $line The line number.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If an invalid value is provided.
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }
}
