<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

/**
 * Handles an exception occurring when a duplicate header is added
 * to a {@link DCurlRequest}.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        rpc_exceptions
 */
class DDuplicateHeaderException extends DRpcException
{
    /**
     * Creates a new {@link DDuplicateHeaderException}.
     *
     * @param    string $header The duplicated header.
     *
     * @return    static
     */
    public function __construct($header)
    {
        parent::__construct(array(
                                'header' => $header,
                            ));
    }
}
