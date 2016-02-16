<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

/**
 * A class that represent a definable element within a database schema.
 *
 * @author        Timothy de Paris
 */
interface DDefinableSchemaElement
{
    /**
     * Returns the name of the schema element.
     *
     * @return    string
     */
    public function getName();
}
