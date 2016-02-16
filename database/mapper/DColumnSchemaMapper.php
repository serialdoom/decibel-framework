<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mapper;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\schema\DColumnDefinition;

/**
 * Provides mapping of column schema statements to a database's query language.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
abstract class DColumnSchemaMapper implements DStatementMapper, DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns a statement that can be used to add the provided column to a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    abstract public function getAddStatement(DColumnDefinition $column);

    /**
     * Returns a statement that can be used to create the provided column in a new table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    abstract public function getCreateStatement(DColumnDefinition $column);

    /**
     * Returns a statement that can be used to drop the provided column from a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    abstract public function getDropStatement(DColumnDefinition $column);

    /**
     * Returns a statement that can be used to modify the provided column in a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    abstract public function getModifyStatement(DColumnDefinition $column);
}
