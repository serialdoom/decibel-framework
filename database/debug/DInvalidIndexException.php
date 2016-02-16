<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an invalid index is referenced.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidIndexException extends DDatabaseException
{
    /**
     * Creates a new {@link DInvalidIndexException}.
     *
     * @param    string $index Name of the index.
     *
     * @return    static
     */
    public function __construct($index)
    {
        parent::__construct(array(
                                'index' => $index,
                            ));
    }
}
