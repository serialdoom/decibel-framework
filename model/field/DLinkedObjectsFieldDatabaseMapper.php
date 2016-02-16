<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\database\DQuery;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\debug\DErrorHandler;
use app\decibel\utility\DPersistable;
use app\decibel\utility\DResult;

/**
 * Provides functionality to map a {@link DLinkedObjectsField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DLinkedObjectsFieldDatabaseMapper extends DOneToManyRelationalFieldDatabaseMapper
{
    /**
     * 'Field Name' database column name.
     *
     * @var        string
     */
    const COLUMN_FIELD_NAME = 'fieldName';
    /**
     * 'From' database column name.
     *
     * @var        string
     */
    const COLUMN_FROM = 'from';
    /**
     * 'Position' database column name.
     *
     * @var        string
     */
    const COLUMN_POSITION = 'position';
    /**
     * 'To' database column name.
     *
     * @var        string
     */
    const COLUMN_TO = 'to';

    /**
     * Implements any functionality required to remove extraneous records
     * from the database associated with this field type.
     *
     * Triggered by {@link app::decibel::database::maintenance::DOptimiseDatabase DOptimiseDatabase}.
     *
     * @return    DResult    The result of the operation, or <code>null</code>
     *                    if no action was performed.
     */
    public static function cleanDatabase()
    {
        $result = new DResult();
        try {
            new DQuery('app\\decibel\\model\\field\\DLinkedObjectsField-cleanDatabase');
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $result->setSuccess(false);
        }

        return $result;
    }

    /**
     * Called when deleting {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @param    DPersistable $instance The model instance being deleted.
     *
     * @return    bool    <code>true</code> if data was deleted,
     *                    <code>false</code> if not.
     */
    public function delete(DPersistable $instance)
    {
        try {
            $query = new DQuery(
                'app\\decibel\\model\\field\\DLinkedObjectsField-delete',
                array(
                    self::COLUMN_FROM       => $instance->getId(),
                    self::COLUMN_FIELD_NAME => $this->fieldName,
                )
            );
            $success = $query->isSuccessful();
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $success = false;
        }

        return $success;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DLinkedObjectsField::class;
    }

    /**
     * Returns SQL representing this field within the database.
     *
     * @param    string $alias            If provided, this alias will be applied
     *                                    to the field in the returned SQL.
     * @param    string $tableSuffix      A suffix to append to the table name.
     *
     * @return    string
     */
    public function getSelectSql($alias = null, $tableSuffix = '')
    {
        $fieldName = $this->fieldName;
        $sql = "GROUP_CONCAT(DISTINCT `decibel_model_field_dlinkedobjectsfield_{$fieldName}{$tableSuffix}`.`to` SEPARATOR ',')";
        if ($alias) {
            $sql .= " AS `{$alias}`";
        }

        return $sql;
    }

    /**
     * Called when loading {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @param    DPersistable $instance       The model instance being loaded.
     * @param    array        $data           Pointer to the data array for
     *                                        the model instance.
     *
     * @return    bool    <code>true</code> if data was loaded,
     *                    <code>false</code> if not.
     */
    public function load(DPersistable $instance, array &$data)
    {
        $fieldName = $this->fieldName;
        try {
            $query = new DQuery('app\\decibel\\model\\field\\DLinkedObjectsField-load', array(
                self::COLUMN_FROM       => $instance->getId(),
                self::COLUMN_FIELD_NAME => $fieldName,
            ));
            while ($row = $query->getNextRow()) {
                $data[ $fieldName ][] = (int)$row[ self::COLUMN_TO ];
            }
            // Where no data was loaded, populate with default data.
            if (!isset($data[ $fieldName ])) {
                $data[ $fieldName ] = $this->field->getStandardDefaultValue();
            }
            $success = true;
        } catch (DDatabaseException $exception) {
            DErrorHandler::logException($exception);
            $success = false;
        }

        return $success;
    }

    /**
     * Called when saving {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @param    DPersistable $instance The model instance being saved.
     *
     * @return    bool    <code>true</code> if data was saved,
     *                    <code>false</code> if not.
     */
    public function save(DPersistable $instance)
    {
        $fieldName = $this->fieldName;
        $orderable = $this->field->orderable;
        $value = $instance->getFieldValue($fieldName);
        // Delete existing records.
        $this->delete($instance);
        // Add new records.
        $success = true;
        foreach ($value as $key => $value) {
            /* @var $value DPersistable */
            if ($orderable) {
                $position = $key;
            } else {
                $position = 0;
            }
            try {
                $query = new DQuery('app\\decibel\\model\\field\\DLinkedObjectsField-save', array(
                    self::COLUMN_FROM       => $instance->getId(),
                    self::COLUMN_FIELD_NAME => $fieldName,
                    self::COLUMN_POSITION   => $position,
                    self::COLUMN_TO         => $value->getId(),
                ));
                // Merge query success.
                $success = $success && $query->isSuccessful();
            } catch (DDatabaseException $exception) {
                DErrorHandler::logException($exception);
                $success = false;
            }
        }

        return $success;
    }
}
