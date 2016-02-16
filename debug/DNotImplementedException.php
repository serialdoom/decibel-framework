<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when a static function has not been
 * implemented by an extending clas.
 *
 * @section        why Why Would I Use It?
 *
 * As PHP does not allow <code>abstract</code> <code>static</code> functions,
 * this exception should be thrown from an abstract class when extending classes
 * are expected to implement a <code>static</code> function.
 *
 * See @ref debugging_exceptions_standard for further information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the PHP <code>throw</code> statement.
 * When creating the exception, the qualified name of the extending class
 * and the <code>static</code> function name should be provided.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that
 * the <code>MyBaseClass::doSomething()</code> abstract function
 * needs to be implemented in the <code>MyClass</code> class:
 *
 * @code
 * namespace app\MyApp;
 *
 * use app\decibel\debug\DNotImplementedException;
 *
 * class MyBaseClass {
 *    public static function doSomething() {
 *        throw new DNotImplementedException(array(get_called_class(), __FUNCTION__));
 *    }
 * }
 *
 * class MyClass {
 * }
 *
 * MyClass::doSomething();
 * @endcode
 *
 * This will halt execution with the message:
 *
 * <em><code>app\\MyApp\\MyClass::doSomething()</code> must be implemented.</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_standard
 */
class DNotImplementedException extends DException
{
    /**
     * Creates a new {@link DNotImplementedException}.
     *
     * @param    callable $method The non-implemented method.
     *
     * @return    static
     */
    public function __construct($method)
    {
        parent::__construct(array(
            'message' => 'Not implemented',
            'method' => $this->formatCallable($method),
        ));
    }
}
