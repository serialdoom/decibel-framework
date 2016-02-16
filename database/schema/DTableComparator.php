<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\database\debug\DInvalidColumnException;
use app\decibel\database\debug\DInvalidIndexException;

/**
 * Provides comparison functionality for table definitions.
 *
 * @author        Timothy de Paris
 */
class DTableComparator extends DSchemaElementComparator
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DTableDefinition::class;
    }

    /**
     * Returns a list of columns that exist in the second definition
     * but not in the first definition.
     *
     * @return    array    List of {@link DColumnDefinition} objects with
     *                    column names as keys.
     */
    public function getAddedColumns()
    {
        $addedColumns = array();
        $columnsA = $this->adaptee->getColumns();
        $columnsB = $this->compareTo->getColumns();
        $namesA = array_keys($columnsA);
        $namesB = array_keys($columnsB);
        foreach (array_diff($namesB, $namesA) as $addedColumnName) {
            $addedColumns[ $addedColumnName ] = $columnsB[ $addedColumnName ];
        }

        return $addedColumns;
    }

    /**
     * Returns a list of indexes that exist in the second definition
     * but not in the first definition.
     *
     * @return    array    List of {@link DIndexDefinition} objects with
     *                    column names as keys.
     */
    public function getAddedIndexes()
    {
        $addedIndexes = array();
        $indexesA = $this->adaptee->getIndexes();
        $indexesB = $this->compareTo->getIndexes();
        $namesA = array_keys($indexesA);
        $namesB = array_keys($indexesB);
        foreach (array_diff($namesB, $namesA) as $addedIndexName) {
            $addedIndexes[ $addedIndexName ] = $indexesB[ $addedIndexName ];
        }

        return $addedIndexes;
    }

    /**
     * Returns a list of columns that exist in the first definition
     * but not in the second definition.
     *
     * @return    array    List of {@link DColumnDefinition} objects with
     *                    column names as keys.
     */
    public function getDroppedColumns()
    {
        $droppedColumns = array();
        $columnsA = $this->adaptee->getColumns();
        $columnsB = $this->compareTo->getColumns();
        $namesA = array_keys($columnsA);
        $namesB = array_keys($columnsB);
        foreach (array_diff($namesA, $namesB) as $droppedColumnName) {
            $droppedColumns[ $droppedColumnName ] = $columnsA[ $droppedColumnName ];
        }

        return $droppedColumns;
    }

    /**
     * Returns a list of indexes that exist in the first definition
     * but not in the second definition.
     *
     * @return    array    List of {@link DIndexDefinition} objects with
     *                    index names as keys.
     */
    public function getDroppedIndexes()
    {
        $droppedIndexes = array();
        $indexesA = $this->adaptee->getIndexes();
        $indexesB = $this->compareTo->getIndexes();
        $namesA = array_keys($indexesA);
        $namesB = array_keys($indexesB);
        foreach (array_diff($namesA, $namesB) as $droppedIndexName) {
            $droppedIndexes[ $droppedIndexName ] = $indexesA[ $droppedIndexName ];
        }

        return $droppedIndexes;
    }

    /**
     * Returns a list of columns that have been modified from the first
     * definition to the second definition.
     *
     * @return    array    List of {@link DColumnDefinition} objects with
     *                    column names as keys.
     */
    public function getModifiedColumns()
    {
        /* @var $table DTableDefinition */
        $table = $this->adaptee;
        $modifiedColumns = array();
        foreach ($this->compareTo->getColumns() as $columnName => $secondColumn) {
            /* @var $secondColumn DColumnDefinition */
            // Retrieve column with the same name from the first definition.
            try {
                $firstColumn = $table->getColumn($columnName);
            } catch (DInvalidColumnException $exception) {
                continue;
            }
            // Check if there are any differences.
            $comparator = DSchemaElementComparator::adapt($firstColumn)
                                                  ->compareTo($secondColumn);
            if ($comparator->hasChanges()) {
                $modifiedColumns[ $columnName ] = $secondColumn;
            }
        }

        return $modifiedColumns;
    }

    /**
     * Returns a list of indexes that have been modified from the first
     * definition to the second definition.
     *
     * @return    array    List of {@link DIndexDefinition} objects with
     *                    index names as keys.
     */
    public function getModifiedIndexes()
    {
        /* @var $table DTableDefinition */
        $table = $this->adaptee;
        $modifiedIndexes = array();
        foreach ($this->compareTo->getIndexes() as $indexName => $secondIndex) {
            /* @var $secondIndex DIndexDefinition */
            // Retrieve index with the same name from the first definition.
            try {
                $firstIndex = $table->getIndex($indexName);
            } catch (DInvalidIndexException $exception) {
                continue;
            }
            // Check if there are any differences.
            $comparator = DSchemaElementComparator::adapt($firstIndex)
                                                  ->compareTo($secondIndex);
            if ($comparator->hasChanges()) {
                $modifiedIndexes[] = $secondIndex;
            }
        }

        return $modifiedIndexes;
    }

    /**
     * Determines if the compared schema elements are different.
     *
     * @return    bool
     */
    public function hasChanges()
    {
        return null;
    }
}
