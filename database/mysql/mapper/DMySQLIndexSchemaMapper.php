<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\mapper;

use app\decibel\database\mapper\DIndexSchemaMapper;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\schema\DIndexDefinition;

/**
 * Provides mapping of index schema statements to to the MySQL query language.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DMySQLIndexSchemaMapper extends DIndexSchemaMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DMySQL::class;
    }

    /**
     * Returns a statement that can be used to add the provided index to a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    public function getAddStatement(DIndexDefinition $index)
    {
        return "ADD {$this->getIndexStatement($index)}";
    }

    /**
     * Returns a statement that can be used to create the provided index in a new table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    public function getCreateStatement(DIndexDefinition $index)
    {
        return $this->getIndexStatement($index);
    }

    /**
     * Returns a statement that can be used to drop the provided index from a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    public function getDropStatement(DIndexDefinition $index)
    {
        $name = $index->getName();
        $type = $index->getType();
        if ($type === DMySQL::INDEX_TYPE_PRIMARY) {
            $statement = 'DROP PRIMARY KEY';
        } else {
            $statement = "DROP INDEX `{$name}`";
        }

        return $statement;
    }

    /**
     * Returns a statement that defines the index.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    protected function getIndexStatement(DIndexDefinition $index)
    {
        $name = $index->getName();
        $type = $index->getType();
        if ($type === DMySQL::INDEX_TYPE_PRIMARY) {
            $prefix = 'PRIMARY KEY';
        } else {
            if ($type === DMySQL::INDEX_TYPE_STANDARD) {
                $prefix = "KEY `{$name}`";
            } else {
                $prefix = "{$type} KEY `{$name}`";
            }
        }

        return "{$prefix} (`" . implode('`, `', $index->getColumns()) . '`)';
    }

    /**
     * Returns a statement that can be used to modify the provided index in a table.
     *
     * @param    DIndexDefinition $index
     *
     * @return    string
     */
    public function getModifyStatement(DIndexDefinition $index)
    {
        return "{$this->getDropStatement($index)}, {$this->getAddStatement($index)}";
    }
}
