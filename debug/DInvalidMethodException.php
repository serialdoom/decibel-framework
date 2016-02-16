<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when a non-existent method is called.
 *
 * @section    why Why Would I Use It?
 *
 * This exception should be thrown when a non-existent method is called.
 *
 * See @ref debugging_exceptions_standard for further information.
 *
 * @section    how How Do I Use It?
 *
 * The exception should be thrown using the {@link DErrorHandler::throwException()}
 * function or the PHP <code>throw</code> statement. When creating the exception,
 * the owner and method name should be provided as a <code>callable</code>.
 *
 * @note
 * Using the {@link DErrorHandler::throwException()} function ensures that
 * this exception will not interrupt application execution when %Decibel
 * is running in @ref configuration_mode_production.
 * See @ref debugging_exceptions_throwing for further information.
 *
 * @subsection example Examples
 *
 * The following example throws an exception showing that the <code>getSubject</code>
 * method is not valid for the <code>MyClass</code> class:
 *
 * @code
 * namespace app\MyApp;
 *
 * use app\decibel\debug\DInvalidMethodException;
 *
 * class MyClass {
 *
 *    protected $title;
 *
 *    public function __call($name, $arguments) {
 *
 *        switch ($name) {
 *
 *            case 'getTitle':
 *                return $this->title;
 *
 *            default:
 *                throw new DInvalidMethodException(array(get_called_class(), $name));
 *
 *        }
 *    }
 * }
 *
 * $class = new MyClass();
 * echo $class->getSubject();
 * @endcode
 *
 * This will halt execution with the message:
 *
 * <em>Called method <code>app\MyApp\MyClass::geSubject()</code> does not exist.</em>
 *
 * @section    versioning Version Control
 *
 * @author     Timothy de Paris
 * @ingroup    debugging_standard
 */
class DInvalidMethodException extends DException
{
    /**
     * Creates a new {@link DInvalidMethodException}.
     *
     * @param    callable $method
     *
     * @return    static
     */
    public function __construct($method)
    {
        parent::__construct(array(
                                'method' => $this->formatCallable($method),
                            ));
    }
}
