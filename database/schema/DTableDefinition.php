<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\debug\DInvalidColumnException;
use app\decibel\database\debug\DInvalidIndexException;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\database\mapper\DColumnSchemaMapper;
use app\decibel\database\mapper\DIndexSchemaMapper;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DErrorHandler;
use app\decibel\server\DServer;

/**
 * Provides information about a table.
 *
 * @author        Nikolay Dimitrov
 */
class DTableDefinition extends DSchemaElementDefinition
    implements DDebuggable
{
    /**
     * Name of the table being defined.
     *
     * @var        string
     */
    protected $tableName;
    /**
     * Columns.
     *
     * @var        array
     */
    protected $columns = array();
    /**
     * Indexes.
     *
     * @var        array
     */
    protected $indexes = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'tableName' => $this->tableName,
            'columns'   => $this->columns,
            'indexes'   => $this->indexes,
        );
    }

    /**
     * Constructs an instance of this class
     *
     * @param    string $tableName Name of the database table this represents.
     *
     * @return    static
     */
    public function __construct($tableName = null)
    {
        parent::__construct();
        $this->tableName = $tableName;
    }

    /**
     * Adds a column to the definition.
     *
     * @param    DColumnDefinition $column The column.
     *
     * @return    void
     */
    public function addColumn(DColumnDefinition $column)
    {
        $column->setTable($this);
        $this->columns[ $column->getName() ] = $column;
    }

    /**
     * Adds a list of columns to the definition.
     *
     * @param    array $columns List of {@link DColumnDefinition} objects.
     *
     * @return    void
     */
    public function addColumns(array $columns)
    {
        foreach ($columns as $column) {
            /* @var $column DColumnDefinition */
            $this->addColumn($column);
        }
    }

    /**
     * Adds an index to the definition.
     *
     * @param    DIndexDefinition $index The index.
     *
     * @return    void
     */
    public function addIndex(DIndexDefinition $index)
    {
        $index->setTable($this);
        $this->indexes[ $index->getName() ] = $index;
    }

    /**
     * Adds a list of indexes to the definition.
     *
     * @param    array $indexes List of {@link DIndexDefinition} objects.
     *
     * @return    void
     */
    public function addIndexes(array $indexes)
    {
        foreach ($indexes as $index) {
            /* @var $index DIndexDefinition */
            $this->addIndex($index);
        }
    }

    /**
     * Returns the index with the specified name.
     *
     * @param    string $name
     *
     * @return    DIndexDefinition
     * @throws    DInvalidIndexException    If the requested index does not exist.
     */
    public function getIndex($name)
    {
        if (!isset($this->indexes[ $name ])) {
            throw new DInvalidIndexException($name);
        }

        return $this->indexes[ $name ];
    }

    /**
     * Returns the indexes defined by this table.
     *
     * @return    array    List of {@link DColumnDefinition} objects.
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Creates a new DTableDefinition object.
     *
     * @param    string    $tableName Name of the table.
     * @param    DDatabase $database  The database in which the table exists.
     *
     * @return    DTableDefinition
     */
    public static function createFromTable($tableName,
                                           DDatabase $database = null)
    {
        // Load the application database if none provided.
        if ($database === null) {
            $database = DDatabase::getDatabase();
        }
        // Handle existing tables.
        $databaseInformation = DDatabaseInformation::adapt($database);
        $tableNames = $databaseInformation->getTableNames();
        if (!in_array($tableName, $tableNames)) {
            $definition = null;
        } else {
            $dbInfo = DDatabaseInformation::adapt($database);
            $definition = $dbInfo->getTableInformation($tableName);
        }

        return $definition;
    }

    /**
     * Returns create SQL.
     *
     * @return    string
     */
    public function getCreateSql()
    {
        if (count($this->columns) === 0) {
            return false;
        }
        $database = DDatabase::getDatabase();
        $columnSchemaMapper = DColumnSchemaMapper::adapt($database);
        $indexSchemaMapper = DIndexSchemaMapper::adapt($database);
        $fieldsAndIndexes = array();
        foreach ($this->columns as $column) {
            /* @var $column DColumnDefinition */
            $fieldsAndIndexes[] = $columnSchemaMapper->getCreateStatement($column);
        }
        foreach ($this->indexes as $index) {
            /* @var $index DIndexDefinition */
            $fieldsAndIndexes[] = $indexSchemaMapper->getCreateStatement($index);
        }

        return sprintf('CREATE TABLE IF NOT EXISTS `%s` (
						  %s
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;',
                       $this->tableName,
                       implode(",\n", $fieldsAndIndexes)
        );
    }

    /**
     * Returns the definition of a column defined by this table
     * with the specified name.
     *
     * @note
     * Column names are treated differently depending on the operating
     * system on which Decibel is running. For Windows operating systems,
     * column names are not case-sensitive. On *nix operating systems,
     * column names are case-sensitive.
     *
     * @param    string $name Name of the column to return.
     *
     * @return    DColumnDefinition    The column definition.
     * @throws    DInvalidColumnException    If the column does not exist in this table.
     */
    public function getColumn($name)
    {
        $definition = null;
        // Handle Windows operating systems.
        if (DServer::isWindows()) {
            $lowerCaseName = strtolower($name);
            // Check each field individually as case must be ignored.
            // Otherwise Decibel may try to re-create a column that already
            // exists with the wrong case.
            foreach ($this->columns as $columnName => $column) {
                /* @var $column DColumnDefinition */
                if (strtolower($columnName) === $lowerCaseName) {
                    $definition = $column;
                    break;
                }
            }
            // *nix operating systems.
        } else {
            if (isset($this->columns[ $name ])) {
                $definition = $this->columns[ $name ];
            }
        }
        if ($definition === null) {
            throw new DInvalidColumnException($name);
        }

        return $definition;
    }

    /**
     * Returns the columns defined by this table.
     *
     * @return    array    List of {@link DColumnDefinition} objects.
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns the name of the table represented by this definition.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * Checks if a specified column exists in this definition.
     *
     * @param    DColumnDefinition $column The column to check for.
     * @param    bool              $strict If <code>true</code>, the column name and type
     *                                     must match, otherwise only the name will
     *                                     be checked.
     *
     * @return    bool
     */
    public function hasColumn(DColumnDefinition $column, $strict = true)
    {
        // Check whether a column with this name exists.
        $columnName = $column->getName();
        if (!isset($this->columns[ $columnName ])) {
            $hasColumn = false;
            // If this isn't a strict check, the column just needs to exist.
        } else {
            if (!$strict) {
                $hasColumn = true;
                // Otherwise perform a strict check.
            } else {
                $thisColumn = $this->columns[ $columnName ];
                $hasColumn = !DSchemaElementComparator::adapt($column)
                                                      ->compareTo($thisColumn)
                                                      ->hasChanges();
            }
        }

        return $hasColumn;
    }

    /**
     * Compare the two definitions and apply changes to the database
     * if needed. The definition provided as parameter is the new
     * definition that represents the desired result.
     *
     * Note that some data might be lost when performing this operation!
     *
     * @param    DTableDefinition $anotherDefinition
     * @param    bool             $dropColumns
     *
     * @return    void
     */
    public function mergeWith(DTableDefinition $anotherDefinition,
                              $dropColumns = false)
    {
        $operations = array();
        /* @var $comparator DTableComparator */
        $comparator = DSchemaElementComparator::adapt($this)
                                              ->compareTo($anotherDefinition);
        $database = DDatabase::getDatabase();
        $columnSchemaMapper = DColumnSchemaMapper::adapt($database);
        $indexSchemaMapper = DIndexSchemaMapper::adapt($database);
        // Check for fields that should be added.
        foreach ($comparator->getAddedColumns() as $column) {
            /* @var $column DColumnDefinition */
            $operations[] = $columnSchemaMapper->getAddStatement($column);
        }
        // Check for fields that should be modified.
        foreach ($comparator->getModifiedColumns() as $column) {
            /* @var $column DColumnDefinition */
            $operations[] = $columnSchemaMapper->getModifyStatement($column);
        }
        // Check for fields that should be dropped.
        if ($dropColumns) {
            foreach ($comparator->getDroppedColumns() as $column) {
                /* @var $column DColumnDefinition */
                $operations[] = $columnSchemaMapper->getDropStatement($column);
            }
        }
        // Check for indexes that should be added.
        foreach ($comparator->getAddedIndexes() as $index) {
            /* @var $index DIndexDefinition */
            $operations[] = $indexSchemaMapper->getAddStatement($index);
        }
        // Check for indexes that should be modified.
        foreach ($comparator->getModifiedIndexes() as $index) {
            /* @var $index DIndexDefinition */
            $operations[] = $indexSchemaMapper->getModifyStatement($index);
        }
        // Check for indexes that should be dropped.
        foreach ($comparator->getDroppedIndexes() as $index) {
            /* @var $index DIndexDefinition */
            $operations[] = $indexSchemaMapper->getDropStatement($index);
        }
        if (count($operations)) {
            try {
                // Apply the required changes.
                $sql = "ALTER TABLE `{$this->tableName}` " . implode(', ', $operations);
                new DQuery($sql);
                // Analyze table after altering it to rebuild indexes.
                new DQuery("ANALYZE TABLE `{$this->tableName}`");
            } catch (DQueryExecutionException $exception) {
                DErrorHandler::throwException($exception);
            }
        }
    }

    /**
     * Resets the values of all fields to their default value.
     *
     * @return    void
     */
    public function resetFieldValues()
    {
        parent::resetFieldValues();
        $this->tableName = null;
        $this->columns = array();
        $this->indexes = array();
    }

    /**
     * Sets the name of the table represented by this definition.
     *
     * @param    string $name The new table name.
     *
     * @return    static    This instance, for chaining
     */
    public function setName($name)
    {
        $this->tableName = $name;

        return $this;
    }

    /**
     * Creates new table with this definition.
     *
     * @return    void
     */
    public function createTable()
    {
        $sql = $this->getCreateSql();
        if ($sql !== false) {
            new DQuery($sql);
        }
    }
}
