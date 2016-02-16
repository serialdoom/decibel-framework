<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DProcessException;

/**
 * Handles an exception occurring when attempting to interact with
 * a process that does not exist.
 *
 * @author        Timothy de Paris
 */
class DInvalidProcessIdException extends DProcessException
{
    /**
     * Creates a new {@link DInvalidProcessIdException}.
     *
     * @param    int $pid The process ID.
     *
     * @return    static
     */
    public function __construct($pid)
    {
        parent::__construct(array(
                                'pid' => $pid,
                            ));
    }
}

