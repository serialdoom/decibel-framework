<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when a model is saved by an event handler
 * while it is already in the process of being saved.
 *
 * @author        Timothy de Paris
 */
class DRecursiveModelSaveException extends DModelException
{
    /**
     * Creates a new {@link DRecursiveModelSaveException}.
     *
     * @param    string $qualifiedName Qualified name of the model.
     * @param    int    $id            Model instance ID.
     *
     * @return  static
     */
    public function __construct($qualifiedName, $id)
    {
        parent::__construct(array(
                                'qualifiedName' => $qualifiedName,
                                'id'            => $id,
                            ));
    }
}
