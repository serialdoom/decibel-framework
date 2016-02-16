<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when a definition attempts to register
 * a configuration option with a pre-existing name.
 *
 * @author        Timothy de Paris
 */
class DDuplicateConfigurationOptionException extends DModelException
{
    /**
     * Creates a new {@link DDuplicateConfigurationOptionException}.
     *
     * @param    string $name  Name of the configuration option.
     * @param    string $model Qualified name of the model class.
     *
     * @return    static
     */
    public function __construct($name, $model)
    {
        parent::__construct(array(
                                'name'  => $name,
                                'model' => $model,
                            ));
    }
}
