<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an attempt is made to prune a table that is in use.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidPrunableTableException extends DDatabaseException
{
    /**
     * Creates a new {@link DInvalidPrunableTableException}.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    static
     */
    public function __construct($tableName)
    {
        parent::__construct(array(
                                'tableName' => $tableName,
                            ));
    }
}
