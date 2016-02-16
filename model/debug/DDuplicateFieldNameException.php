<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when a definition attempts to register
 * a field with a pre-existing name.
 *
 * @author        Timothy de Paris
 */
class DDuplicateFieldNameException extends DModelException
{
    /**
     * Creates a new {@link DDuplicateFieldNameException}.
     *
     * @param    string $name         Name of the field.
     * @param    string $model        Qualified name of the model class.
     * @param    string $existing     Qualified name of the model class
     *                                the field has already been added to.
     *
     * @return    static
     */
    public function __construct($name, $model, $existing)
    {
        parent::__construct(array(
                                'name'     => $name,
                                'model'    => $model,
                                'existing' => $existing,
                            ));
    }
}
