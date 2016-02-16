<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\debug;

/**
 * Handles an exception occurring when invalid parameters
 * are assigned to a cookie.
 *
 * @author        Timothy de Paris
 */
class DInvalidCookieException extends DCookieException
{
    /**
     * Creates a new {@link DInvalidCookieException}.
     *
     * @param    string $reason Reason for the cookie being invalid.
     *
     * @return    static
     */
    public function __construct($reason)
    {
        parent::__construct(array(
                                'reason' => $reason,
                            ));
    }
}
