<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\event;

use app\decibel\reflection\DReflectionClass;

/**
 * Reflects a {@link DEvent} object.
 *
 * @author    Timothy de Paris
 */
class DEventReflection extends DReflectionClass
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
        if (!$this->isAbstract()) {
            $event = new $qualifiedName();
            $this->fields = $event->getFields();
        }
    }
}
