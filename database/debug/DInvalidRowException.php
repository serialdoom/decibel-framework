<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an invalid row is requested
 * from query results.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidRowException extends DDatabaseException
{
    /**
     * Creates a new DInvalidRowException.
     *
     * @param    int $rowNumber Number of the row that doesn't exist.
     *
     * @return    static
     */
    public function __construct($rowNumber)
    {
        parent::__construct(array(
                                'rowNumber' => $rowNumber,
                            ));
    }
}
