<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration\debug;

/**
 * Handles an exception occurring when an unknown configuration option
 * is requested.
 *
 * @author        Timothy de Paris
 */
class DUnknownConfigurationOptionException extends DConfigurationException
{
    /**
     * Creates a new {@link DUnknownConfigurationOptionException}.
     *
     * @param    string $option The configuration option.
     *
     * @return    static
     */
    public function __construct($option)
    {
        parent::__construct(array(
                                'option' => $option,
                            ));
    }
}
