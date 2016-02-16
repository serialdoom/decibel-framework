<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

/**
 * Handles an exception occurring when an invalid result set is provided
 * when creating a {@link DQueryTester} object.
 *
 * @author        Timothy de Paris
 */
class DInvalidResultsException extends DTestingException
{
    /**
     * Creates a new {@link DInvalidResultsException}.
     *
     * @param    int $columnCount The number of columns.
     *
     * @return    static
     */
    public function __construct($columnCount)
    {
        parent::__construct(array(
                                'columnCount' => $columnCount,
                            ));
    }
}
