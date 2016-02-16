<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

use app\decibel\regional\DLabel;

/**
 * Handles an exception occurring during query execution within a database.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DQueryExecutionException extends DDatabaseException
{
    /**
     * Creates a new DQueryExecutionException.
     *
     * @param    int    $errorCode The database error code.
     * @param    string $message   The database error message.
     *
     * @return    static
     */
    public function __construct($errorCode, $message)
    {
        $this->code = $errorCode;
        parent::__construct(array($message));
    }

    /**
     * Generates the exception message, using the provided variables.
     *
     * @param    array $variables
     *
     * @return    DLabel
     */
    protected function generateMessage(array $variables)
    {
        return array_pop($variables);
    }
}
