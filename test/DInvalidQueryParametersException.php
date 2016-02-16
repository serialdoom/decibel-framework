<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

/**
 * Handles an exception occurring when invalid parameters are provided
 * to a tested {@link DQueryTester} object.
 *
 * @author        Timothy de Paris
 */
class DInvalidQueryParametersException extends DTestingException
{
    /**
     * Creates a new {@link DInvalidQueryParametersException}.
     *
     * @param    array $actual   The provided parameters.
     * @param    array $expected The expected parameters.
     *
     * @return    static
     */
    public function __construct(array $actual, array $expected)
    {
        parent::__construct(array(
                                'actual'   => var_export($actual, true),
                                'expected' => var_export($expected, true),
                            ));
    }
}
