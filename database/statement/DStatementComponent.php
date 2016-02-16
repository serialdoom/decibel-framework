<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\statement;

use app\decibel\utility\DUtilityData;

/**
 * Represents a component of a statement that can be executed against a database.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
abstract class DStatementComponent extends DUtilityData
{
    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
    }
}
