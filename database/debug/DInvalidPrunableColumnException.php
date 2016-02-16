<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an attempt is made to prune a column that is in use.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidPrunableColumnException extends DDatabaseException
{
    /**
     * Creates a new {@link DInvalidPrunableColumnException}.
     *
     * @param    string $tableName  Name of the table the column belongs to.
     * @param    string $columnName Name of the column.
     *
     * @return    static
     */
    public function __construct($tableName, $columnName)
    {
        parent::__construct(array(
                                'tableName'  => $tableName,
                                'columnName' => $columnName,
                            ));
    }
}
