<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an invalid column is requested
 * from query results.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidColumnException extends DDatabaseException
{
    /**
     * Creates a new DInvalidColumnException.
     *
     * @param    string $column Name of the column.
     *
     * @return    static
     */
    public function __construct($column)
    {
        parent::__construct(array(
                                'column' => $column,
                            ));
    }
}
