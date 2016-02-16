<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

use app\decibel\debug\DException;

/**
 * Handles an exception occurring in a remote procedure.
 *
 * @section        why Why Would I Use It?
 *
 * This class can be extended to create new remote procedure related exceptions.
 *
 * @section        how How Do I Use It?
 *
 * See @ref debugging_exceptions_custom for information about writing custom
 * exceptions.
 *
 * @section        example Examples
 *
 * Following is an example of a custom remote procedure exception class:
 *
 * @code
 * namesapce app\MyApp;
 *
 * class DMyRpcException extends app\decibel\rpc\DRpcException {
 *    const message = 'Unable to execute remote procedure <code>%s</code> at this time.';
 * }
 * @endcode
 *
 * The custom exception can then be thrown using the following code:
 *
 * @code
 * throw new app\MyApp\DMyRpcException('app\\MyApp\\MyRpc');
 * @endcode
 *
 * This will generate the following message:
 *
 * <em>Unable to execute remote procedure <code>app\\MyApp\\MyRpc</code> at this time.</em>
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
abstract class DRpcException extends DException
{
}
