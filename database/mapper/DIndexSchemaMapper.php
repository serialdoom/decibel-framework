<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mapper;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\schema\DIndexDefinition;

/**
 * Provides mapping of index schema statements to a database's query language.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
abstract class DIndexSchemaMapper implements DStatementMapper, DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns a statement that can be used to add the provided index to a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    abstract public function getAddStatement(DIndexDefinition $index);

    /**
     * Returns a statement that can be used to create the provided index in a new table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    abstract public function getCreateStatement(DIndexDefinition $index);

    /**
     * Returns a statement that can be used to drop the provided index from a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    abstract public function getDropStatement(DIndexDefinition $index);

    /**
     * Returns a statement that can be used to modify the provided index in a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    abstract public function getModifyStatement(DIndexDefinition $index);
}
