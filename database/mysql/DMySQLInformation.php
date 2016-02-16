<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\mysql;

use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\DQuery;
use app\decibel\database\schema\DColumnDefinition;
use app\decibel\database\schema\DIndexDefinition;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\debug\DInvalidFieldValueException;

/**
 * MySQL database information decorator class.
 *
 * @author    Timothy de Paris
 */
class DMySQLInformation extends DDatabaseInformation
{
    /**
     * 'Default Value' column information key.
     *
     * @var        string
     */
    const SHOW_COLUMNS_DEFAULT = 'Default';

    /**
     * 'Column Name' column information key.
     *
     * @var        string
     */
    const SHOW_COLUMNS_NAME = 'Field';

    /**
     * 'Column Type' column information key.
     *
     * @var        string
     */
    const SHOW_COLUMNS_TYPE = 'Type';

    /**
     * 'Column Name' index information key.
     *
     * @var        string
     */
    const SHOW_INDEXES_COLUMN = 'Column_name';

    /**
     * 'Index Name' index information key.
     *
     * @var        string
     */
    const SHOW_INDEXES_NAME = 'Key_name';

    /**
     * 'Non-unique' index information key.
     *
     * @var        string
     */
    const SHOW_INDEXES_NON_UNIQUE = 'Non_unique';

    /**
     * 'Type' index information key.
     *
     * @var        string
     */
    const SHOW_INDEXES_TYPE = 'Index_type';

    /**
     * Names of tables in this database.
     *
     * Cached here after first accessed by {@link DMySQLInformation::getTableNames()}.
     *
     * @var        array
     */
    protected $tableNames;

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
     * Determines the type of index represented by the provided
     * index information.
     *
     * @param    array $row       Information returned from
     *                            a "SHOW INDEXES" query.
     *
     * @return    string
     */
    private function getIndexType(array $row)
    {
        if ($row[ self::SHOW_INDEXES_NAME ] === DMySQL::INDEX_TYPE_PRIMARY) {
            $type = DMySQL::INDEX_TYPE_PRIMARY;
        } else {
            if ($row[ self::SHOW_INDEXES_TYPE ] === DMySQL::INDEX_TYPE_FULLTEXT) {
                $type = DMySQL::INDEX_TYPE_FULLTEXT;
            } else {
                if ((int)$row[ self::SHOW_INDEXES_NON_UNIQUE ] === 1) {
                    $type = DMySQL::INDEX_TYPE_STANDARD;
                } else {
                    $type = DMySQL::INDEX_TYPE_UNIQUE;
                }
            }
        }

        return $type;
    }

    /**
     * Returns the number of rows and physical size of the database.
     *
     * @param    string $prefix If specified, only tables with this prefix will be included.
     *
     * @return    array    An array containing the following fields:
     *                    - <code>size</code>: The physical size of the database.
     *                    - <code>rows</code>: The number of rows of data stored by the database.
     */
    public function getSize($prefix = false)
    {
        $tableInfo = $this->getTableInfo();
        $result = array(
            'size' => 0,
            'rows' => 0,
        );
        for ($i = 0; $i < sizeof($tableInfo); $i++) {
            if ($prefix === false
                || strpos($tableInfo[ $i ]['Name'], $prefix) === 0
            ) {
                $result['size'] += (int)($tableInfo[ $i ]['Data_length'] + $tableInfo[ $i ]['Index_length']);
                $result['rows'] += (int)$tableInfo[ $i ]['Rows'];
            }
        }

        return $result;
    }

    /**
     * Return list of currently available columns for a given table.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    array    List of {@link app::decibel::database::DColumnDefinition DColumnDefinition}
     *                    objects.
     */
    protected function getTableColumns($tableName)
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        $columnQuery = new DQuery(
            "SHOW COLUMNS FROM `#tableName#`",
            array('tableName' => $tableName),
            $database
        );
        $columns = array();
        while ($column = $columnQuery->getNextRow()) {
            $columns[] = $this->getTableColumn($column);
        }

        return $columns;
    }

    /**
     * Returns a {@link DColumnDefinition} object that represents a MySQL table column.
     *
     * @param    array $column Result of MySQL "SHOW COLUMNS" query.
     *
     * @return    DColumnDefinition
     */
    protected function getTableColumn(array $column)
    {
        $definition = new DColumnDefinition($column[ self::SHOW_COLUMNS_NAME ]);
        try {
            $this->getTableColumnType($column[ self::SHOW_COLUMNS_TYPE ], $definition);
            // Just in case we don't know about this column type.
        } catch (DInvalidFieldValueException $exception) {
            DErrorHandler::logException($exception);
        }
        $definition->setUnsigned(stripos($column[ self::SHOW_COLUMNS_TYPE ], 'unsigned') !== false);
        $definition->setNull($column['Null'] === 'YES');
        $definition->setAutoincrement($column['Extra'] === 'auto_increment');
        if ($column[ self::SHOW_COLUMNS_DEFAULT ] !== null) {
            $definition->setDefaultValue(
                $definition->castValueForField($column[ self::SHOW_COLUMNS_DEFAULT ])
            );
        }

        return $definition;
    }

    /**
     * Determines the type for a column.
     *
     * @param    string            $type       'Type' column from a MySQL "SHOW COLUMNS" result.
     * @param    DColumnDefinition $definition The definition to find the type for.
     *
     * @return    void
     * @throws    DInvalidFieldValueException    If the provided type is not know.
     */
    protected function getTableColumnType($type, DColumnDefinition $definition)
    {
        $matches = null;
        if (preg_match('/([a-zA-Z]+)\(?([0-9]+)?\)?/', $type, $matches)) {
            $definition->setType($matches[1]);
            if (isset($matches[2])
                && stripos($matches[1], 'int') === false
            ) {
                $definition->setSize((int)$matches[2]);
            }
        }
    }

    /**
     * Return list of currently available fields for a given table.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    array    List of {@link app::decibel::database::DIndexDefinition DIndexDefinition}
     *                    objects.
     */
    protected function getTableIndexes($tableName)
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        // Add indexes (manually because they are multirow)
        $query = new DQuery(
            "SHOW INDEXES FROM `#tableName#`",
            array('tableName' => $tableName),
            $database
        );
        // Index information is returned over multiple rows,
        // so we need to iterate this to build each index.
        // $indexName stores the name of the index currently being processed
        // and will be used to detect when the rows for the index have
        // all been processed.
        $indexName = null;
        $index = null;
        $indexes = array();
        while ($row = $query->getNextRow()) {
            $this->processIndexRow($row, $indexes, $indexName, $index);
        }
        // Add the last index processed to the list.
        if ($index !== null) {
            $indexes[] = $index;
        }

        return $indexes;
    }

    /**
     * Returns an array containing information about each table
     * in the database.
     *
     * @return    array
     */
    public function getTableInfo()
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        $tableInfo = array();
        $tableQuery = $database->query("SHOW TABLE STATUS FROM `{$database->getDatabaseName()}`;");
        while ($table = $database->getNextRow($tableQuery)) {
            $tableInfo[] = $table;
        }

        return $tableInfo;
    }

    /**
     * Returns an array containing the names of each table in the database.
     *
     * @return    array
     */
    public function getTableNames()
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        // Check instance cache.
        if ($this->tableNames === null) {
            $tableQuery = $database->query("SHOW TABLES;");
            $this->tableNames = array();
            while ($tableName = $database->getNextRow($tableQuery)) {
                $this->tableNames[] = array_pop($tableName);
            }
        }

        return $this->tableNames;
    }

    /**
     * Processes a row from a SHOW INDEXES query performed by
     * the {@link DMySQLInformation::getTableIndexes()} function.
     *
     * @param    array            $row
     * @param    array            $indexes
     * @param    string           $indexName
     * @param    DIndexDefinition $index
     *
     * @return    void
     */
    protected function processIndexRow(array $row, array &$indexes, &$indexName,
                                       DIndexDefinition &$index = null)
    {
        // Start a new index, $indexName has changed.
        if ($indexName !== $row[ self::SHOW_INDEXES_NAME ]) {
            // If there is an existing index, add it to the list
            // before we move on to the next one.
            if ($index !== null) {
                $indexes[] = $index;
            }
            // Remember the new index name.
            $indexName = $row[ self::SHOW_INDEXES_NAME ];
            // Create a new index.
            $index = new DIndexDefinition(
                $row[ self::SHOW_INDEXES_NAME ],
                $this->getIndexType($row)
            );
        }
        $index->addColumn($row[ self::SHOW_INDEXES_COLUMN ]);
    }
}
