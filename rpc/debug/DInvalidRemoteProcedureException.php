<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

/**
 * Handles an exception occurring when execution of a non-existent remote
 * procedure is attempted.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DRemoteProcedureCall} class when
 * attempting execution of a remote procedure that does not exist
 * on the target server.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of the {@link DRemoteProcedureCall}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DInvalidRemoteProcedureException}.
 *
 * @code
 * use app\decibel\rpc\DRemoteProcedureCall;
 *
 * try {
 *    $result = DRemoteProcedureCall::create('app\\MyApp\\MyInvalidRemoteProcedure')
 *        ->setServer('myremoteserver.com')
 *        ->execute();
 * } catch (DInvalidRemoteProcedureException $e) {
 *    debug('Requested remote procedure does not exist!');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
class DInvalidRemoteProcedureException extends DRpcException
{
    /**
     * Creates a new {@link DInvalidRemoteProcedureException}.
     *
     * @param    string $qualifiedName Qualified name of the invalid rpc.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct(array(
                                'qualifiedName' => $qualifiedName,
                            ));
    }
}
