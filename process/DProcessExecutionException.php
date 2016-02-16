<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\process;

use app\decibel\process\DProcessException;

/**
 * Handles an exception occurring when executing a process.
 *
 * @author        Timothy de Paris
 */
class DProcessExecutionException extends DProcessException
{
    /**
     * Creates a new {@link DProcessExecutionException}.
     *
     * @param    string $command      The executed command.
     * @param    string $stdErr       Optional output provided on STDERR
     *                                by the failed process.
     *
     * @return    static
     */
    public function __construct($command, $stdErr = '')
    {
        parent::__construct(array(
                                'command' => $command,
                                'stdErr'  => $stdErr,
                            ));
    }

    /**
     * Returns output provided on STDERR by the failed process.
     *
     * @return    string
     */
    public function getStdErr()
    {
        return $this->information['stdErr'];
    }
}
