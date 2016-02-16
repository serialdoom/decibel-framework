<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql\mapper;

use app\decibel\database\mapper\DColumnSchemaMapper;
use app\decibel\database\mysql\DMySQL;
use app\decibel\database\schema\DColumnDefinition;

/**
 * Provides mapping of column schema statements to the MySQL query language.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DMySQLColumnSchemaMapper extends DColumnSchemaMapper
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
     * Returns a statement that can be used to add the provided column to a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    public function getAddStatement(DColumnDefinition $column)
    {
        return "ADD {$this->getColumnStatement($column)}";
    }

    /**
     * Returns a statement that can be used to create the provided column in a new table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    public function getCreateStatement(DColumnDefinition $column)
    {
        return $this->getColumnStatement($column);
    }

    /**
     * Returns a statement that can be used to drop the provided column from a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    public function getDropStatement(DColumnDefinition $column)
    {
        return "DROP `{$column->getName()}`";
    }

    /**
     * Returns a statement that can be used to modify the provided column in a table.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    public function getModifyStatement(DColumnDefinition $column)
    {
        return "MODIFY {$this->getColumnStatement($column)}";
    }

    /**
     * Returns an SQL statement that defines the column in the database.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    protected function getColumnStatement(DColumnDefinition $column)
    {
        $name = $column->getName();
        $type = $column->getType();
        $sql = "`{$name}` {$type}";
        // Add a size if appropriate for this type of column.
        $size = $column->getSize();
        if ($size !== null) {
            $sql .= "({$size})";
        }
        // Add any flags.
        $sql .= $this->getColumnFlags($column);
        // Append the default value if any.
        $defaultValue = $this->getDefaultValue($column);
        if ($defaultValue !== null) {
            $sql .= " DEFAULT {$defaultValue}";
        }

        return $sql;
    }

    /**
     * Returns the SQL representation of flags for the column.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string
     */
    protected function getColumnFlags(DColumnDefinition $column)
    {
        $flags = array();
        if ($column->getUnsigned()) {
            $flags[] = ' UNSIGNED';
        }
        if ($column->getNull()) {
            $flags[] = ' NULL';
        } else {
            $flags[] = ' NOT NULL';
        }
        if ($column->getAutoincrement()) {
            $flags[] = ' AUTO_INCREMENT';
        }

        return implode('', $flags);
    }

    /**
     * Returns the default value for this column, ready for insertion into SQL.
     *
     * @param    DColumnDefinition $column
     *
     * @return    string    The default value, or <code>null</code> if there
     *                    is no default value.
     */
    protected function getDefaultValue(DColumnDefinition $column)
    {
        $columnDefaultValue = $column->getDefaultValue();
        if ($column->getAutoincrement()) {
            $defaultValue = null;
        } else {
            if ($column->getNull()
                && $columnDefaultValue === null
            ) {
                $defaultValue = 'NULL';
            } else {
                if ($columnDefaultValue !== null) {
                    $defaultValue = "'" . addcslashes($columnDefaultValue, "'\\") . "'";
                } else {
                    $defaultValue = null;
                }
            }
        }

        return $defaultValue;
    }
}
