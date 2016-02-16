<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\debug;

/**
 * Handles an exception occurring when an invalid parameter value is passed
 * to a query.
 *
 * See @ref database_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        database_exceptions
 */
class DInvalidParameterValueException extends DDatabaseException
{
    /**
     * Creates a new {@link DInvalidParameterValueException}.
     *
     * @param    string $parameter The parameter that was provided an invalid value.
     *
     * @return    static
     */
    public function __construct($parameter)
    {
        parent::__construct(array(
                                'parameter' => $parameter,
                            ));
    }
}
