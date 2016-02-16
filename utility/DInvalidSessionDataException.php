<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * Handles an exception occurring when invalid data is stored in the session.
 *
 * @author        Timothy de Paris
 */
class DInvalidSessionDataException extends DSessionException
{
    /**
     * Creates a new {@link DInvalidSessionDataException}.
     *
     * @param    string $reason Reason for the session data being invalid.
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
