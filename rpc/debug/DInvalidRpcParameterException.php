<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

/**
 * Handles an exception occurring when an invalid parameter value is supplied
 * to a remote procedure.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown from the
 * {@link DRemoteProcedure::validateParameters()} function of any class
 * extending {@link DRemoteProcedure} when an invalid parameter value
 * is encountered.
 *
 * See @ref rpc_writing_errors in the @ref rpc Developer Guide for futher
 * information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the PHP <code>throw</code> statement.
 * When creating the exception, the name of parameter and the expected value
 * should be passed as parameters.
 *
 * @note
 * When thrown inside the {@link DRemoteProcedure::validateParameters()} function
 * of any class extending {@link DRemoteProcedure}, application execution will
 * not be halted as the exception will be automatically caught and managed.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the 'value'
 * parameter must be a positive integer.
 *
 * @code
 * throw new DInvalidRpcParameterException('value', 'positive integer');
 * @endcode
 *
 * This will generate the following message:
 *
 * <em>Invalid value provided for parameter <code>value</code>, expected: positive integer</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
class DInvalidRpcParameterException extends DRpcException
{
    /**
     * Creates a new {@link DInvalidRpcParameterException}.
     *
     * @param    mixed  $value     The invalid value.
     * @param    string $parameter Name of the parameter
     * @param    string $expected  Description of the expected value.
     *
     * @return    static
     */
    public function __construct($value, $parameter, $expected)
    {
        parent::__construct(array(
                                'value'     => (string)$value,
                                'parameter' => $parameter,
                                'expected'  => $expected,
                            ));
    }
}
