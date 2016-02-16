<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when a recursive function call is detected.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        debugging_standard
 */
class DRecursionException extends DException
{
    /**
     * Creates a new {@link DRecursionException}.
     *
     * @param    callable $method The method in which recursion was detected.
     *
     * @return    static
     */
    public function __construct(callable $method)
    {
        parent::__construct(array(
                                'method' => $this->formatCallable($method),
                            ));
    }
}
