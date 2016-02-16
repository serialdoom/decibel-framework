<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when an invalid value is assigned
 * to an object instance parameter or method parameter.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown when provision of invalid
 * parameter data occurs.
 *
 * See @ref debugging_exceptions_standard for further information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the {@link DErrorHandler::throwException()}
 * function or the PHP <code>throw</code> statement. When creating the exception,
 * the name of the parameter and the expected value should be provided.
 *
 * @note
 * Using the {@link DErrorHandler::throwException()} function ensures that
 * this exception will not interrupt application execution when %Decibel
 * is running in @ref configuration_mode_production.
 * See @ref debugging_exceptions_throwing for further information.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the <code>title</code>
 * parameter must contain a <code>string</code> value:
 *
 * @code
 * namespace app\MyApp;
 *
 * use app\decibel\debug\DInvalidPropertyException;
 * use app\decibel\debug\DInvalidParameterValueException;
 *
 * class MyClass {
 *
 *    protected $title;
 *
 *    public function setTitle($title) {
 *
 *        if (!is_string($title)) {
 *            throw new DInvalidParameterValueException('name', array('app\\MyApp\\MyClass', 'setTitle'), 'string');
 *        }
 *
 *        $this->title = $title;
 *
 *    }
 * }
 *
 * $class = new MyClass();
 * $class->title = false;
 * @endcode
 *
 * This will halt execution with the message:
 *
 * <em>Invalid value provided for parameter <code>title</code> of
 * <code>app\\MyApp\\MyClass</code>, expected: string</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_standard
 */
class DInvalidParameterValueException extends DException
{
    /**
     * Creates a new {@link DInvalidParameterValueException}.
     *
     * @param    string   $name           Name of the parameter.
     * @param    callable $owner          Qualified name of the class or function
     *                                    that owns the parameter.
     * @param    bool     $expected       The expected value.
     *
     * @return    static
     */
    public function __construct($name, $owner, $expected)
    {
        parent::__construct(array(
                                'name'     => $name,
                                'owner'    => $this->formatCallable($owner),
                                'expected' => $expected,
                            ));
    }
}
