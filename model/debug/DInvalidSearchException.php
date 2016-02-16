<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when an invalid search is attempted.
 *
 * @author        Timothy de Paris
 */
class DInvalidSearchException extends DModelSearchException
{
    /**
     * Creates a new {@link DInvalidSearchException}.
     *
     * @param    string $reason Reason for the search being invalid.
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
