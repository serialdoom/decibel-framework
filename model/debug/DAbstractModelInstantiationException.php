<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when code requests the instantiation
 * of an abstract model.
 *
 * @author        Timothy de Paris
 */
class DAbstractModelInstantiationException extends DModelException
{
    /**
     * Creates a new {@link DAbstractModelInstantiationException}.
     *
     * @param    string $model Qualified name of the model class.
     *
     * @return    static
     */
    public function __construct($model)
    {
        parent::__construct(array(
                                'model' => $model,
                            ));
    }
}
