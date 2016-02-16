<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when a method is called in an invalid way.
 *
 * @author        Timothy de Paris
 */
class DInvalidMethodCallException extends DException
{
    /**
     * Creates a new {@link DInvalidMethodCallException}.
     *
     * @param    callable $method The method.
     * @param    string   $reason Reason the call was invalid.
     *
     * @return    static
     */
    public function __construct(callable $method, $reason = null)
    {
        parent::__construct(array(
                                'method' => $this->formatCallable($method),
                                'reason' => $reason,
                            ));
    }
}
