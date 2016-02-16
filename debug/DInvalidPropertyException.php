<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when a non-existent property is requested
 * from an object.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown when a request is made for a non-existent
 * property value.
 *
 * See @ref debugging_exceptions_standard for further information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the {@link DErrorHandler::throwException()}
 * function or the PHP <code>throw</code> statement. When creating the exception,
 * the name of the requested property should be provided.
 *
 * @note
 * Using the {@link DErrorHandler::throwException()} function ensures that
 * this exception will not interrupt application execution when %Decibel
 * is running in @ref configuration_mode_production.
 * See @ref debugging_exceptions_throwing for further information.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the <code>subject</code>
 * property is not valid for the <code>MyClass</code> class:
 *
 * @code
 * namespace app\MyApp;
 *
 * use app\decibel\debug\DInvalidPropertyException;
 *
 * class MyClass {
 *
 *    protected $title;
 *
 *    public function __get($name) {
 *        switch ($name) {
 *
 *            case 'title':
 *                return $this->title;
 *
 *            default:
 *                throw new DInvalidPropertyException($name, get_called_class());
 *        }
 *    }
 * }
 *
 * $class = new MyClass();
 * echo $class->subject;
 * @endcode
 *
 * This will halt execution with the message:
 *
 * <em>No parameter exists with the name <code>subject</code></em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_standard
 */
class DInvalidPropertyException extends DException
{
    /**
     * Creates a new {@link DInvalidPropertyException}.
     *
     * @param    string $name     Name of the parameter.
     * @param    string $owner    Qualified name of the class or function
     *                            that owns the parameter.
     *
     * @return    static
     */
    public function __construct($name, $owner = null)
    {
        parent::__construct(array(
                                'name'  => $name,
                                'owner' => $owner,
                            ));
    }
}
