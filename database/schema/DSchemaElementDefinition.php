<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\utility\DUtilityData;

/**
 * Defines an element of a database schema.
 *
 * @author    Timothy de Paris
 */
abstract class DSchemaElementDefinition extends DUtilityData
    implements DAdaptable, DDefinableSchemaElement
{
    use DAdapterCache;

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    { }
}
