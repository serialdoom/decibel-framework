<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event\debug;

/**
 * Handles an exception occurring when a required parameter is omitted
 * from the <code>$parameters</code> variable when creating an emailable event.
 *
 * See @ref events_exceptions for further information.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        events_exceptions
 */
class DMissingEventParameterException extends DEventException
{
    /**
     * Creates a new {@link DMissingEventParameterException}.
     *
     * @param    string $parameter Name of the missing parameter.
     * @param    string $event     Qualified name of the event.
     *
     * @return    static
     */
    public function __construct($parameter, $event)
    {
        parent::__construct(array(
                                'parameter' => $parameter,
                                'event'     => $event,
                            ));
    }
}
