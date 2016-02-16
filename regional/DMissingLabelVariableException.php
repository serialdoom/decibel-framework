<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

/**
 * Handles an exception occurring when a variable value is not provided
 * while rendering a label.
 *
 * @author        Timothy de Paris
 */
class DMissingLabelVariableException extends DRegionalException
{
    /**
     * Creates a new {@link DMissingLabelVariableException}.
     *
     * @param    string $variable  The variable name.
     * @param    string $namespace Namespace of the label.
     * @param    string $name      Name of the label.
     *
     * @return    static
     */
    public function __construct($variable, $namespace, $name)
    {
        parent::__construct(array(
                                'variable'  => $variable,
                                'namespace' => $namespace,
                                'name'      => $name,
                            ));
    }
}
