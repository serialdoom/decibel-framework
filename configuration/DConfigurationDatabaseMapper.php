<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\application\DClassManager;
use app\decibel\application\DConfigurationManager;
use app\decibel\database\DQuery;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\decorator\DDecorator;
use app\decibel\model\DBaseModel;
use app\decibel\utility\DResult;

/**
 * Provides functionality to map configuration option values
 * to the database via SQL statements.
 *
 * @author    Timothy de Paris
 */
class DConfigurationDatabaseMapper extends DDecorator
{
    /**
     * 'qualifiedName' database column.
     *
     * @var        string
     */
    const COLUMN_QUALIFIED_NAME = 'qualifiedName';

    /**
     * 'name' database column.
     *
     * @var        string
     */
    const COLUMN_NAME = 'name';

    /**
     * 'value' database column.
     *
     * @var        string
     */
    const COLUMN_VALUE = 'value';

    /**
     * SQL query to update a class option.
     *
     * @note
     * It is possible that this is needed before the stored procedures are
     * loaded (specifically to convert pre-6.7.1 configuration files).
     *
     * @var        string
     */
    const SQL_UPDATE_CLASS_OPTION = "REPLACE INTO `decibel_application_dconfigurationmanager` SET `qualifiedName`='#qualifiedName#', `name`='#name#', `value`='#value#'";

    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DConfigurationManager::class;
    }

    /**
     * Stores the value for a class configuration option.
     *
     * @param    string $for
     * @param    string $option
     * @param    string $value
     *
     * @return    void
     */
    protected function loadClassConfigurationValue($for, $option, $value)
    {
        if (DClassManager::isValidClassName($for, DBaseModel::class)) {
            $definition = $for::getDefinition();
            $field = $definition->getConfiguration($option);
        } else {
            if (DClassManager::isValidClassName($for, 'app\\decibel\\configuration\\DConfigurable')) {
                $configurationClass = $for::getConfigurationClass();
                $configuration = new $configurationClass();
                try {
                    $field = $configuration->getField($option);
                    // Just in case the field has been removed...
                } catch (DInvalidPropertyException $exception) {
                    $field = null;
                }
            } else {
                $field = null;
            }
        }
        if ($field !== null) {
            $mapper = $field->getDatabaseMapper();
            $value = $mapper->unserialize($value);
            $this->setClassConfiguration($for, $option, $value);
        }
    }

    /**
     * Updates the value for a class configuration option in the database.
     *
     * @param    string $for    Qualified name of the configurable class.
     * @param    string $option Configuration option to update.
     * @param    mixed  $value  Updated value.
     *
     * @return    DResult
     */
    public function updateClassConfiguration($for, $option, $value)
    {
        $result = new DResult();
        if (is_array($value)) {
            $value = serialize($value);
        }
        try {
            new DQuery(self::SQL_UPDATE_CLASS_OPTION, array(
                self::COLUMN_QUALIFIED_NAME => $for,
                self::COLUMN_NAME           => $option,
                self::COLUMN_VALUE          => $value,
            ));
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::logException($exception);
            $result->setSuccess(DResult::TYPE_ERROR, $exception->getMessage());
        }

        return $result;
    }
}
