<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

/**
 * Handles an exception occurring when an invalid variable value is provided
 * while rendering a label.
 *
 * @author        Timothy de Paris
 */
class DInvalidLabelVariableException extends DRegionalException
{
    /**
     * Creates a new {@link DInvalidLabelVariableException}.
     *
     * @param    mixed  $value     The invalid value.
     * @param    string $variable  The variable name.
     * @param    string $namespace Namespace of the label.
     * @param    string $name      Name of the label.
     *
     * @return    static
     */
    public function __construct($value, $variable, $namespace, $name)
    {
        parent::__construct(array(
                                'value'     => $value,
                                'variable'  => $variable,
                                'namespace' => $namespace,
                                'name'      => $name,
                            ));
    }
}
