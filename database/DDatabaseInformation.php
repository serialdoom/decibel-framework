<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\schema\DTableDefinition;

/**
 * Database information decorator class.
 *
 * This class provides functions to retrieve information about
 * its associated {@link DDatabase} class.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseInformation implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns the number of rows and physical size of the database.
     *
     * @param    string $prefix If specified, only tables with this prefix will be included.
     *
     * @return    array    An array containing the following values:
     *                    - <code>size</code>: The physical size of the database.
     *                    - <code>rows</code>: The number of rows of data stored by the database.
     */
    abstract public function getSize($prefix = false);

    /**
     * Return information about available columns for a given table.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    array    List of {@link app::decibel::database::DColumnDefinition DColumnDefinition}
     *                    objects.
     */
    abstract protected function getTableColumns($tableName);

    /**
     * Return list of currently available indexes for a given table.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    array    List of {@link app::decibel::database::DIndexDefinition DIndexDefinition}
     *                    objects.
     */
    abstract protected function getTableIndexes($tableName);

    /**
     * Returns an array containing information about each table
     * in the database.
     *
     * @return    array
     */
    abstract public function getTableInfo();

    /**
     * Return information about a given table.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    DTableDefinition
     */
    public function getTableInformation($tableName)
    {
        $table = new DTableDefinition($tableName);
        // Add column.
        $columns = $this->getTableColumns($tableName);
        $table->addColumns($columns);
        // Add indexes.
        $indexes = $this->getTableIndexes($tableName);
        $table->addIndexes($indexes);

        return $table;
    }

    /**
     * Returns an array containing the names of each table in the database.
     *
     * This is called by the {@link DDatabase::__get()} method when
     * retrieving the <code>tables</code> magic property.
     *
     * @return    array
     */
    abstract public function getTableNames();
}
