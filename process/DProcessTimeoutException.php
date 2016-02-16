<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DProcessException;

/**
 * Handles an exception occurring when an executing process times out.
 *
 * @author        Timothy de Paris
 */
class DProcessTimeoutException extends DProcessException
{
    /**
     * Creates a new {@link DProcessTimeoutException}.
     *
     * @param    string $command The executed command.
     *
     * @return    static
     */
    public function __construct($command)
    {
        parent::__construct(array(
                                'command' => $command,
                            ));
    }
}
