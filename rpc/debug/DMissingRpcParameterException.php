<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

/**
 * Handles an exception occurring when a required parameter is not supplied
 * to a remote procedure.
 *
 * @section        why Why Would I Use It?
 *
 * This exception should be thrown from the
 * {@link DRemoteProcedure::validateParameters()} function of any class
 * extending {@link DRemoteProcedure} when a required parameter value
 * is not provided.
 *
 * See @ref rpc_writing_errors in the @ref rpc Developer Guide for futher
 * information.
 *
 * @section        how How Do I Use It?
 *
 * The exception should be thrown using the PHP <code>throw</code> statement.
 * When creating the exception, the name of the required parameter
 * should be passed as a parameter.
 *
 * @subsection     example Examples
 *
 * The following example throws an exception showing that the 'value'
 * parameter must be provided.
 *
 * @code
 * throw new DMissingRpcParameterException('value');
 * @endcode
 *
 * @note
 * When thrown inside the {@link DRemoteProcedure::validateParameters()} function
 * of any class extending {@link DRemoteProcedure}, application execution will
 * not be halted as the exception will be automatically caught and managed.
 *
 * This will generate the following message:
 *
 * <em>Required parameter <code>value</code> has not been provided.</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
class DMissingRpcParameterException extends DRpcException
{
    /**
     * Creates a new {@link DMissingRpcParameterException}.
     *
     * @param    string $parameter Name of the parameter.
     *
     * @return    static
     */
    public function __construct($parameter)
    {
        parent::__construct(array(
                                'parameter' => $parameter,
                            ));
    }
}
