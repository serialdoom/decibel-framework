<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when an attempt is made to change
 * a read-only parameter.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown when an assignment is made
 * to a read-only parameter
 *
 * See @ref debugging_exceptions_standard for further information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the {@link DErrorHandler::throwException()}
 * function or the PHP <code>throw</code> statement. When creating the exception,
 * the name of the read-only parameter should be provided.
 *
 * @note
 * Using the {@link DErrorHandler::throwException()} function ensures that
 * this exception will not interrupt application execution when %Decibel
 * is running in @ref configuration_mode_production.
 * See @ref debugging_exceptions_throwing for further information.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the <code>age</code>
 * parameter is read-only:
 *
 * @code
 * namespace app\MyApp;
 *
 * use app\decibel\debug\DInvalidPropertyException;
 * use app\decibel\debug\DInvalidParameterValueException;
 * use app\decibel\debug\DReadOnlyParameterException;
 *
 * class MyClass {
 *
 *    protected $title;
 *
 *    public function __set($name, $value) {
 *        switch ($name) {
 *
 *            case 'title':
 *                if (!is_string($title)) {
 *                    throw new DInvalidParameterValueException('title', 'app\\MyApp\\MyClass', 'string');
 *                }
 *                $this->title = $title;
 *
 *            case 'age':
 *                throw new DReadOnlyParameterException($name, get_class($this));
 *
 *            default:
 *                throw new DInvalidPropertyException($name);
 *        }
 *    }
 * }
 *
 * $class = new MyClass();
 * $class->age = 10;
 * @endcode
 *
 * This will halt execution with the message:
 *
 * <em>The value of parameter <code>age</code> cannot be modified.</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_standard
 */
class DReadOnlyParameterException extends DException
{
    /**
     * Creates a new {@link DReadOnlyParameterException}.
     *
     * @param    string $name         Name of the read-only parameter.
     * @param    string $owner        Qualified name of the class or function
     *                                that owns the parameter.
     * @param    string $message      Additional message if applicable.
     *
     * @return    static
     */
    public function __construct($name, $owner, $message = '')
    {
        parent::__construct(array(
                                'name'    => $name,
                                'owner'   => $owner,
                                'message' => $message,
                            ));
    }
}
