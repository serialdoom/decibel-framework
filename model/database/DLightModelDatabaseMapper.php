<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\database;

use app\decibel\model\DLightModel;

/**
 * Provides functionality to map a {@link DLightModel} to the database.
 *
 * @author    Timothy de Paris
 */
class DLightModelDatabaseMapper extends DDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DLightModel::class;
    }

    /**
     * Returns SQL queries or stored procedure names required to delete
     * the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    public function getDeleteSql()
    {
        return array('app\\decibel\\model\\DBaseModel-delete');
    }

    /**
     * Returns SQL queries or stored procedure names required to load
     * data from the database for the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    public function getLoadSql()
    {
        return array('app\\decibel\\model\\DBaseModel-load');
    }

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
    public function getSaveSql(array $changedFields)
    {
        $fieldSqls = $this->getSaveFieldSql($changedFields);
        $fieldNames = array_keys($fieldSqls);
        if (count($changedFields) === 0) {
            $sql = null;
        } else {
            if ($this->id === 0) {
                $fields = '`' . implode('`, `', $fieldNames) . '`';
                $values = "'#" . implode("#', '#", $fieldNames) . "#'";
                $sql = "INSERT INTO `#tableName#` ({$fields}) VALUES ({$values})";
            } else {
                $fields = implode(', ', $fieldSqls);
                $sql = "UPDATE `#tableName#` SET {$fields} WHERE `#tableName#`.`id`=#id#";
            }
        }

        return $sql;
    }

    /**
     * Returns SQL segments for each changed field to be updated in the database.
     *
     * @param    array $changedFields     Names of the fields containing
     *                                    updated data for this object.
     *
     * @return    array
     */
    protected function getSaveFieldSql(array $changedFields)
    {
        // Serialise data all fields are required to be included in the
        // serialised data array as all fields could be updated in the save
        // query depending on versioning status.
        $fieldSqls = array();
        $definitionFields = $this->definition->getFields();
        foreach ($changedFields as $fieldName) {
            /* @var $field DField */
            $field = $definitionFields[ $fieldName ];
            // We don't need `id`
            if ($fieldName === 'id') {
                continue;
            }
            // Ignore non-native fields
            $fieldSql = $field->getFieldSql();
            if ($fieldSql === null) {
                continue;
            }
            // Prepare the update SQL for the field.
            $fieldSqls[ $fieldName ] = "{$fieldSql}='#{$fieldName}#'";
        }

        return $fieldSqls;
    }

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
    public function getSerialisedData()
    {
        /* @var $instance DLightModel */
        $instance = $this->getDecorated();
        // Serialise data all fields are required to be included in the
        // serialised data array as all fields could be updated in the save
        // query depending on versioning status.
        $data = $instance->getFieldValues();
        $serialisedData = array();
        foreach ($instance->definition->getFields() as $fieldName => $field) {
            /* @var $field DField */
            $serialisedData[ $fieldName ] = $data[ $fieldName ];
        }
        // Add additional fields required by sql query.
        $serialisedData['id'] = $instance->getId();
        $serialisedData[ DLightModel::FIELD_QUALIFIED_NAME ] = get_class($instance);
        $serialisedData['tableName'] = $this->getTableName();

        return $serialisedData;
    }
}
