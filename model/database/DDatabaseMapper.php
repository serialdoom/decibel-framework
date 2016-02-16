<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\database;

use app\decibel\database\DQuery;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\utility\DResult;

/**
 * Provides functionality to map a model to the database.
 *
 * @author    Timothy de Paris
 */
abstract class DDatabaseMapper extends DRuntimeDecorator
{
    /**
     * Executes the query returned by the {@link DDatabaseMapper::getDeleteSql()}
     * method to remove the model from the database.
     *
     * @return    DResult
     */
    public function delete()
    {
        $result = new DResult();
        try {
            new DQuery(
                $this->getDeleteSql(),
                array(
                    'tableName' => $this->getTableName(),
                    'id'        => $this->getId(),
                ),
                $this->getDatabase()
            );
        } catch (DQueryExecutionException $exception) {
            $result->setSuccess(false, $exception->getMessage());
        }

        return $result;
    }

    /**
     * Returns SQL queries or stored procedure names required to delete
     * the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    abstract public function getDeleteSql();

    /**
     * Returns SQL queries or stored procedure names required to load
     * data from the database for the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    abstract public function getLoadSql();

    /**
     * Returns SQL queries or stored procedure names required to save
     * the specified changed fields for this object to the database.
     *
     * @param    array $changedFields     Names of the fields containing
     *                                    updated data for this object.
     *
     * @return    array    The SQL queries or stored procedure names,
     *                    or <code>null<code> if no query is required.
     */
    abstract public function getSaveSql(array $changedFields);

    /**
     * Returns a list of serialised data which can be passed to a database
     * query to save changes to a model instance.
     *
     * @note
     * This method must return all data, not just data that has been
     * modified since the instance was last saved.
     *
     * @return    array    List of serialised data, with field names as keys.
     */
    abstract public function getSerialisedData();

    /**
     * Returns the name of the table in which data for this model instance
     * will be stored.
     *
     * @return    string
     */
    public function getTableName()
    {
        $qualifiedName = get_class($this->getDecorated());

        return static::getTableNameFor($qualifiedName);
    }

    /**
     * Generates a table name from the specified qualified name.
     *
     * @param    string $qualifiedName
     *
     * @return    string
     */
    public static function getTableNameFor($qualifiedName)
    {
        return strtolower(str_replace(
                              array('app\\', '\\'),
                              array('', '_'),
                              $qualifiedName
                          ));
    }
}
