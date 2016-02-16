<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\maintenance;

use app\decibel\application\DAppInformation;
use app\decibel\cache\DPublicCache;
use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\debug\DInvalidPrunableColumnException;
use app\decibel\database\debug\DInvalidPrunableTableException;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\database\DTableManifest;
use app\decibel\database\schema\DColumnDefinition;
use app\decibel\database\schema\DSchemaElementComparator;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\DDefinition;
use app\decibel\model\DModel;
use app\decibel\registry\DClassQuery;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\stream\DFileStream;

/**
 * Determines whether extraneous fields and tables are present in the database
 * and allows for these to be removed.
 *
 * @author    Timothy de Paris
 */
class DDatabasePruner
{
    /**
     * Internal cache of defined tables.
     *
     * @var        array
     */
    private $definedTables;
    /**
     * Internal cache of prunable fields.
     *
     * @var        array
     */
    private $prunableFields;
    /**
     * Internal cache of prunable tables.
     *
     * @var        array
     */
    private $prunableTables;

    /**
     * Finds the tables that have been defined for use by this application.
     *
     * @return    array
     */
    protected function findDefinedTables()
    {
        $definedTables = array();
        foreach (self::getPersistableClasses() as $model) {
            /* @var $modelDefinition DDefinition */
            $modelDefinition = $model::getDefinition();
            $definedTables[ DDatabaseMapper::getTableNameFor($model) ] = $modelDefinition->getTableDefinition();
        }
        $registry = DGlobalRegistry::load();
        $appInformation = $registry->getHive(DAppInformation::class);
        foreach ($appInformation->getTableFiles() as $manifestFile) {
            $stream = new DFileStream(DECIBEL_PATH . $manifestFile);
            $manifest = new DTableManifest($stream);
            $definedTables = array_merge(
                $definedTables,
                $manifest->getTableDefinitions()
            );
        }

        return $definedTables;
    }

    /**
     * Locates any fields which can be pruned from the database.
     *
     * @return    array
     */
    protected function findPrunableFields()
    {
        $prunableFields = array();
        $expectedDefinitions = $this->getDefinedTables();
        foreach ($expectedDefinitions as $tableName => $expectedDefinition) {
            /* @var $expectedDefinition DTableDefinition */
            // Check existing tables only.
            $currentDefinition = DTableDefinition::createFromTable($tableName);
            if (!$currentDefinition) {
                continue;
            }
            $comparator = DSchemaElementComparator::adapt($currentDefinition)
                                                  ->compareTo($expectedDefinition);
            foreach ($comparator->getDroppedColumns() as $droppedColumn) {
                /* @var $droppedColumn DColumnDefinition */
                $prunableFields[] = array($tableName, $droppedColumn->getName());
            }
        }

        return $prunableFields;
    }

    /**
     * Locates any tables which can be pruned from the database.
     *
     * @return    array
     */
    protected function findPrunableTables()
    {
        $tableNamesNeeded = array();
        $expectedDefinitions = $this->getDefinedTables();
        foreach (array_keys($expectedDefinitions) as $tableName) {
            $tableNamesNeeded[] = $tableName;
        }
        // Check if all needed tables are there
        $database = DDatabase::getDatabase();
        $databaseInformation = DDatabaseInformation::adapt($database);
        $existingTables = $databaseInformation->getTableNames();

        return array_diff($existingTables, $tableNamesNeeded);
    }

    /**
     * Returns a list of tables required by installed Apps.
     *
     * @return    array    List of {@link DTableDefinition} objects.
     */
    protected function getDefinedTables()
    {
        if ($this->definedTables === null) {
            $this->definedTables = $this->findDefinedTables();
        }

        return $this->definedTables;
    }

    /**
     * Returns a list of all persistable classes.
     *
     * @return    array
     */
    protected static function getPersistableClasses()
    {
        $persistable = DClassQuery::load()
                                  ->setAncestor('app\\decibel\\utility\\DPersistable')
                                  ->getClassNames();
        $abstractModels = DClassQuery::load()
                                     ->setAncestor(DModel::class)
                                     ->setFilter(DClassQuery::FILTER_ABSTRACT)
                                     ->getClassNames();
        $abstractModels[] = DModel::class;

        return array_merge($persistable, $abstractModels);
    }

    /**
     * Returns a list of fields that can be pruned from the database.
     *
     * @return    array
     */
    public function getPrunableFields()
    {
        if ($this->prunableFields === null) {
            $this->prunableFields = $this->findPrunableFields();
        }

        return $this->prunableFields;
    }

    /**
     * Returns a list of tables that can be pruned from the database.
     *
     * @return    array
     */
    public function getPrunableTables()
    {
        if ($this->prunableTables === null) {
            $this->prunableTables = $this->findPrunableTables();
        }

        return $this->prunableTables;
    }

    /**
     * Determines if the provided column is prunable.
     *
     * @param    string $tableName  Name of the table the column belongs to.
     * @param    string $columnName Name of the column.
     *
     * @return    bool
     */
    public function isPrunableColumn($tableName, $columnName)
    {
        $prunable = true;
        foreach ($this->getPrunableFields() as $prunableColumn) {
            if ($prunableColumn[0] == $tableName
                && $prunableColumn[1] == $columnName
            ) {
                $prunable = false;
                break;
            }
        }

        return $prunable;
    }

    /**
     * Determines if the provided table is prunable.
     *
     * @param    string $tableName Name of the table.
     *
     * @return    bool
     */
    public function isPrunableTable($tableName)
    {
        return in_array($tableName, $this->getPrunableTables());
    }

    /**
     * Prunes a column from a table in the database.
     *
     * @param    string $tableName  Name of the table to prune the column from.
     * @param    string $columnName Name of the column.
     *
     * @return    bool    <code>true</code> if the column is successfully pruned.
     *                    This method will always throw an exception on failure.
     * @throws    DInvalidPrunableColumnException    If the column cannot be pruned, due to it being
     *                                            defined as in use by an App.
     * @throws    DQueryExecutionException        If the query could not be executed.
     */
    public function pruneColumn($tableName, $columnName)
    {
        if ($this->isPrunableColumn($tableName, $columnName)) {
            new DQuery("ALTER TABLE `{$tableName}` DROP `{$columnName}`");
        } else {
            throw new DInvalidPrunableColumnException($tableName, $columnName);
        }

        return true;
    }

    /**
     * Prunes a table from the database.
     *
     * @param    string $tableName The table to prune.
     *
     * @return    bool    <code>true</code> if the column is successfully pruned.
     *                    This method will always throw an exception on failure.
     * @throws    DInvalidPrunableColumnException    If the column cannot be pruned, due to it being
     *                                            defined as in use by an App.
     * @throws    DQueryExecutionException        If the query could not be executed.
     */
    public function pruneTable($tableName)
    {
        if ($this->isPrunableTable($tableName)) {
            new DQuery("DROP TABLE `{$tableName}`");
            // Clear cached table information.
            $publicCache = DPublicCache::load();
            $publicCache->remove('app\\decibel\\database\\DMySQLInformation_tableNames');
        } else {
            throw new DInvalidPrunableTableException($tableName);
        }

        return true;
    }
}
