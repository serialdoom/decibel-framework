<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

use app\decibel\utility\DJson;
use stdClass;

/**
 * Handles an exception occurring when execution of a remote procedure
 * against an non-Decibel server is attempted, or when the remote server
 * cannot be contacted.
 *
 * @section        why Why Would I Use It?
 *
 * This exception is thrown by the {@link DRemoteProcedureCall} class when
 * attempting execution of a remote procedure on a non-Decibel server or when
 * the remote server cannot be contacted.
 *
 * @section        how How Do I Use It?
 *
 * This exception should be caught using a <code>try ... catch</code> block
 * around any execution of {@link DRemoteProcedureCall::create()}.
 *
 * @subsection     example Examples
 *
 * The following example handles a {@link DInvalidRemoteProcedureCallException}.
 *
 * @code
 * use app\decibel\rpc\DRemoteProcedureCall;
 * use app\decibel\rpc\debug\DInvalidRemoteProcedureCallException;
 *
 * try {
 *    $result = DRemoteProcedureCall::create('app\\MyApp\\MyRemoteProcedure')
 *        ->setServer('myremoteserver.com')
 *        ->execute();
 * } catch (DInvalidRemoteProcedureCallException $e) {
 *    debug('Not a Decibel server!');
 * }
 * @endcode
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
class DInvalidRemoteProcedureCallException extends DRpcException
{
    /**
     * 'HTTP Code' information field name.
     *
     * @var        string
     */
    const INFORMATION_HTTP_CODE = 'httpCode';
    /**
     * 'Location' information field name.
     *
     * @var        string
     */
    const INFORMATION_LOCATION = 'location';
    /**
     * 'Result' information field name.
     *
     * @var        string
     */
    const INFORMATION_RESULT = 'result';

    /**
     * Creates a new {@link DInvalidRemoteProcedureCallException}.
     *
     * @param    string $location Location from which the remote procedure was called.
     * @param    mixed  $httpCode The HTTP status code returned by the remote server.
     * @param    mixed  $result   Content returned by the remote procedure.
     *
     * @return    static
     */
    public function __construct($location,
                                $httpCode = null, $result = null)
    {
        parent::__construct(array(
                                self::INFORMATION_LOCATION  => $location,
                                self::INFORMATION_HTTP_CODE => $httpCode,
                                self::INFORMATION_RESULT    => $result,
                            ));
    }

    /**
     * Returns the HTTP code returned by the remote server, if known.
     *
     * @return    int
     */
    public function getHttpCode()
    {
        return $this->information[ self::INFORMATION_HTTP_CODE ];
    }

    /**
     * Returns the content returned by the remote server.
     *
     * @return    string
     */
    public function getResult()
    {
        return $this->information[ self::INFORMATION_RESULT ];
    }

    /**
     * Returns a stdClass object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        $jsonObject = parent::jsonSerialize();
        $jsonObject->result = $this->information[ self::INFORMATION_RESULT ];

        return $jsonObject;
    }
}
