<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\debug;

/**
 * Handles an exception occurring when the inheritance hierarchy of a model
 * definition does not match that of the model it is defining.
 *
 * @author        Timothy de Paris
 */
class DInvalidDefinitionHierarchyException extends DModelException
{
    /**
     * Creates a new {@link DInvalidDefinitionHierarchyException}.
     *
     * @param    string $definition Qualified name of the definition.
     * @param    string $model      Qualified name of the model.
     *
     * @return  static
     */
    public function __construct($definition, $model)
    {
        parent::__construct(array(
                                'definition' => $definition,
                                'model'      => $model,
                            ));
    }
}
