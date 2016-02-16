<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when attempting to execute a query that
 * contains more information than can be accepted by the database server
 * in a single packet.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DPacketSizeException extends DDatabaseException
{
    /**
     * Creates a new DPacketSizeException.
     *
     * @param    int    $maxPacketSize Maximum packet size for the server.
     * @param    int    $querySize     Size of the query.
     * @param    string $sql           The executed SQL.
     *
     * @return    static
     */
    public function __construct($maxPacketSize, $querySize, $sql)
    {
        parent::__construct(array(
                                'maxPacketSize' => $maxPacketSize,
                                'querySize'     => $querySize,
                                'sql'           => $sql,
                            ));
    }
}
