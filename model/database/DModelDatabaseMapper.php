<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\database;

use app\decibel\database\DStoredProcedure;
use app\decibel\model\DDefinition;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DIdField;

/**
 * Provides functionality to map a {@link DModel} to the database.
 *
 * @author    Timothy de Paris
 */
class DModelDatabaseMapper extends DDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DModel::class;
    }

    /**
     * Returns SQL queries or stored procedure names required to delete
     * the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    public function getDeleteSql()
    {
        // Determine parent tables for this object.
        $tables = array();
        $conditions = array();
        foreach ($this->definition->getFields() as $field) {
            /* @var $field DField */
            $this->getDeleteSqlForField($field, $tables, $conditions);
        }
        $tablesSql = implode('`, `', $tables);
        $conditionalSql = implode(' AND ', $conditions);

        return array("DELETE `{$tablesSql}` FROM `{$tablesSql}` WHERE {$conditionalSql}");
    }

    /**
     * Determines delete SQL components required for a field.
     *
     * @param    DField $field      Field to determine delete SQL for.
     * @param    array  $tables     Pointer to which required tables will be added.
     * @param    array  $conditions Pointer to which conditional SQL will be added.
     *
     * @return    void
     */
    protected function getDeleteSqlForField(DField $field,
                                            array &$tables, array &$conditions)
    {
        $fieldTable = $field->table;
        if (!in_array($fieldTable, $tables)
            && $field->isNativeField()
        ) {
            $tables[] = $fieldTable;
            $conditions[] = "`{$fieldTable}`.`id`=#id#";
        }
    }

    /**
     * Returns SQL queries or stored procedure names required to load
     * data from the database for the model instance.
     *
     * @return    array    The SQL queries or stored procedure names.
     */
    public function getLoadSql()
    {
        // Determine parent tables for this object.
        $tables = array();
        $joins = array();
        $where = array('`#tableName#`.`id`=#id#');
        foreach ($this->definition->getFields() as $field) {
            /* @var $field DField */
            $this->getLoadSqlForField($field, $tables, $joins);
        }
        if (count($tables) > 0) {
            $tablesSql = sprintf(' LEFT JOIN (`%s`) ON (%s)', implode('`, `', $tables), implode(' AND ', $joins));
        } else {
            $tablesSql = '';
        }
        $whereSql = implode(' AND ', $where);

        return array("SELECT * FROM `#tableName#`{$tablesSql} WHERE {$whereSql} LIMIT 1");
    }

    /**
     * Determines load SQL components required for a field.
     *
     * @param    DField $field  Field to determine load SQL for.
     * @param    array  $tables Pointer to which required tables will be added.
     * @param    array  $joins  Pointer to which required joins will be added.
     *
     * @return    void
     */
    protected function getLoadSqlForField(DField $field,
                                          array &$tables, array &$joins)
    {
        $thisTable = $this->getTableName();
        $fieldTable = $field->table;
        if ($fieldTable !== $thisTable
            && !in_array($fieldTable, $tables)
            && $field->isNativeField()
        ) {
            $tables[] = $fieldTable;
            $joins[] = "`{$thisTable}`.`id`=`{$fieldTable}`.`id`";
        }
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
        // Determine the tables that require updating.
        $updateTables = null;
        $sharedTables = null;
        $this->getUpdateTables($changedFields, $updateTables, $sharedTables);
        // If there are no tables to update, return null.
        if (count($updateTables) === 0) {
            return null;
        }
        // Now find all of the fields within these tables
        // that need to be included in the SQL.
        $updateFields = null;
        $this->getUpdateFields($changedFields, $updateTables, $updateFields);
        // Generate sql query.
        $modelTable = static::getTableNameFor(DModel::class);
        $action = ($this->id === 0) ? 'firstSave' : 'save';
        $sqlString = array();
        foreach ($updateFields as $table => $fields) {
            if ($table === $modelTable) {
                $template = "App_ModelObject_{$action}_modelobject";
            } else {
                $template = "App_ModelObject_{$action}";
            }
            // Generate field SQL.
            $sqlFields = implode($fields, ',');
            $queries = DStoredProcedure::get($template);
            $firstQuery = array_shift($queries);
            $sqlString[] = sprintf($firstQuery, $table, $sqlFields);
            $sqlString = array_merge($sqlString, $queries);
        }

        return $sqlString;
    }

    /**
     * Determines the fields that must be updated in order to save
     * the specified changed fields.
     *
     * @param    array $changedFields     Names of the fields containing
     *                                    updated data for this object.
     * @param    array $updateTables      Names of the tables that require
     *                                    updating.
     * @param    array $updateFields      Array in which names of fields that
     *                                    require updating will be returned.
     *
     * @return    void
     */
    protected function getUpdateFields(array $changedFields,
                                       array $updateTables, array &$updateFields = null)
    {
        $modelTable = static::getTableNameFor(DModel::class);
        $updateFields = array();
        // If this is a new object, ensure a row is added to all tables
        // in the object's inheritance hierarchy.
        if ($this->id === 0) {
            // Ensure model table is first to generate INSERT ID.
            $updateFields[ $modelTable ] = array();
            foreach ($this->definition->getInheritanceHierarchy() as $qualifiedName) {
                $updateFields[ static::getTableNameFor($qualifiedName) ] = array();
            }
        }
        // Build a list of fields to be included in the SQL.
        $definition = $this->definition;
        foreach ($definition->getFields() as $fieldName => $field) {
            /* @var $field DField */
            // Check that the field can be updated
            // (and that it hasn't already been included!)
            if (!$this->isFieldUpdatable($field)) {
                continue;
            }
            // Ignore unchanged DModel fields (as an UPDATE query is executed).
            $fieldTable = $field->table;
            if ($fieldTable === $modelTable
                && !in_array($fieldName, $changedFields)
            ) {
                continue;
            }
            // If this field's table is being updated, add the field.
            if (in_array($fieldTable, $updateTables)) {
                $updateFields[ $fieldTable ][ $fieldName ] = $field->getUpdateSql();
            }
        }
    }

    /**
     * Determines the tables that must be updated in order to save
     * the specified changed fields.
     *
     * @param    array $changedFields     Names of the fields containing
     *                                    updated data for this object.
     * @param    array $tables            Pointer in which tables that require
     *                                    updating will be returned.
     * @param    array $sharedTables      Pointer in which shared tables that
     *                                    required updating will be returned.
     *
     * @return    void
     */
    protected function getUpdateTables(array $changedFields,
                                       array &$tables = null, array &$sharedTables = null)
    {
        $tables = array();
        $sharedTables = array();
        // Generate SQL for updated fields.
        /* @var $definition DDefinition */
        $definition = $this->definition;
        foreach ($changedFields as $fieldName) {
            // Load field descriptor.
            $field = $definition->getField($fieldName);
            // Check that the field can be updated
            // (and that it hasn't already been included!)
            $fieldTable = $field->table;
            if (in_array($fieldTable, $tables)
                || !$this->isFieldUpdatable($field)
            ) {
                continue;
            }
            $tables[] = $fieldTable;
            if ($field->isSharedTable()) {
                $sharedTables[] = $fieldTable;
            }
        }
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
        /* @var $instance DModel */
        $instance = $this->getDecorated();
        $serialisedData = array();
        foreach ($instance->definition->getFields() as $field) {
            /* @var $field DField */
            $this->getSerialisedDataForField($field, $serialisedData);
        }
        // Add additional fields required by sql query.
        $qualifiedName = get_class($instance);
        $serialisedData['id'] = $instance->getId();
        $serialisedData[ DModel::FIELD_QUALIFIED_NAME ] = $qualifiedName;
        $serialisedData['tableName'] = DDatabaseMapper::getTableNameFor($qualifiedName);

        return $serialisedData;
    }

    /**
     * Adds serialised data for the provided field into the serialised data array.
     *
     * @param    DField $field
     * @param    array  $serialisedData Pointer to the serialised data array.
     *
     * @return    void
     */
    public function getSerialisedDataForField(DField $field, array &$serialisedData)
    {
        // Ignore non-native fields, which will be saved later.
        if ($field->isNativeField()) {
            $fieldName = $field->getName();
            $serialisedData[ $fieldName ] = $this->getSerializedFieldValue($fieldName);
        }
    }

    /**
     * Determines if the specified field can be included in save SQL.
     *
     * @param    DField $field
     *
     * @return    bool
     */
    protected function isFieldUpdatable(DField $field)
    {
        return $field->isNativeField()
        // Ignore special fields.
        && !$field instanceof DIdField;
    }
}
