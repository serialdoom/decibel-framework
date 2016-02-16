<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\debug;

/**
 * Handles an exception occurring when an attempt is made to load
 * a non-existent cookie.
 *
 * @author        Timothy de Paris
 */
class DUnknownCookieException extends DCookieException
{
    /**
     * Creates a new {@link DUnknownCookieException}.
     *
     * @param    string $name Name of the cookie.
     *
     * @return    static
     */
    public function __construct($name)
    {
        parent::__construct(array(
                                'name' => $name,
                            ));
    }
}
