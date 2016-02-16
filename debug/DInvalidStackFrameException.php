<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

/**
 * Handles an exception occurring when an invalid stack frame is requested
 * from a backtrace.
 *
 * @author    Timothy de Paris
 */
class DInvalidStackFrameException extends DException
{
    /**
     * Creates a new {@link DInvalidStackFrameException}.
     *
     * @param    int $depth The requested depth.
     *
     * @return    static
     */
    public function __construct($depth)
    {
        parent::__construct(array(
                                'depth' => $depth,
                            ));
    }
}
