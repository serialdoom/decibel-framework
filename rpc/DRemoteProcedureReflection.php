<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\reflection\DReflectionClass;

/**
 * Reflects a {@link DRemoteProcedure} object.
 *
 * @author    Timothy de Paris
 */
class DRemoteProcedureReflection extends DReflectionClass
{
    /**
     * Creates a reflection of the provided class
     *
     * @param    string $qualifiedName Qualified name of the class to reflect.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $event = $qualifiedName::load();
        $this->fields = $event->getFields();
    }
}
