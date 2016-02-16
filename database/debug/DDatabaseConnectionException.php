<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

use app\decibel\database\DDatabase;

/**
 * Handles an exception occurring when connecting to a database server.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DDatabaseConnectionException extends DDatabaseException
{
    /**
     * Creates a new {@link DDatabaseConnectionException}.
     *
     * @param    DDatabase $database The database that failed to connect.
     *
     * @return    static
     */
    public function __construct(DDatabase $database)
    {
        parent::__construct(array(
                                'hostname'     => $database->getHostname(),
                                'username'     => $database->getUsername(),
                                'databaseName' => $database->getDatabaseName(),
                            ));
    }

    /**
     * Specifies whether it is possible for the application to recover from
     * this type of exception and continue execution.
     *
     * @return    bool
     */
    public function isRecoverable()
    {
        return false;
    }
}
