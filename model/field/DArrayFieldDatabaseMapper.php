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
 * Provides functionality to map a {@link DArrayField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DArrayFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * 'Field Name' database column name.
     *
     * @var        string
     */
    const COLUMN_FIELD_NAME = 'fieldName';
    /**
     * 'ID' database column name.
     *
     * @var        string
     */
    const COLUMN_ID = 'id';
    /**
     * 'Key' database column name.
     *
     * @var        string
     */
    const COLUMN_KEY = 'key';
    /**
     * 'Value' database column name.
     *
     * @var        string
     */
    const COLUMN_VALUE = 'value';

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
            new DQuery('app\\decibel\\model\\field\\DArrayField-cleanDatabase');
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
        $fieldName = $this->fieldName;
        try {
            $query = new DQuery('app\\decibel\\model\\field\\DArrayField-delete', array(
                self::COLUMN_ID         => $instance->getId(),
                self::COLUMN_FIELD_NAME => $fieldName,
            ));
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
        return DArrayField::class;
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
        $field = "`decibel_model_field_darrayfield_{$fieldName}{$tableSuffix}`.`value`";
        $sql = "GROUP_CONCAT(DISTINCT {$field} ORDER BY {$field} SEPARATOR ',')";
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
            $query = new DQuery('app\\decibel\\model\\field\\DArrayField-load', array(
                self::COLUMN_ID         => $instance->getId(),
                self::COLUMN_FIELD_NAME => $fieldName,
            ));
            while ($row = $query->getNextRow()) {
                if ($row[ self::COLUMN_KEY ] !== '') {
                    $data[ $fieldName ][ $row[ self::COLUMN_KEY ] ] = $row[ self::COLUMN_VALUE ];
                } else {
                    $data[ $fieldName ][] = $row[ self::COLUMN_VALUE ];
                }
            }
            // Where no data was loaded, populate with default data.
            if (!isset($data[ $fieldName ])) {
                $defaultValue = $this->field->getStandardDefaultValue();
                $data[ $fieldName ] = $defaultValue;
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
        $value = $instance->getFieldValue($fieldName);
        // Delete existing records.
        $this->delete($instance);
        // Add new records.
        $success = true;
        foreach ($value as $key => $value) {
            try {
                $query = new DQuery('app\\decibel\\model\\field\\DArrayField-save', array(
                    self::COLUMN_ID         => $instance->getId(),
                    self::COLUMN_FIELD_NAME => $fieldName,
                    self::COLUMN_KEY        => $key,
                    self::COLUMN_VALUE      => trim(strip_tags($value)),
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

    /**
     * Restores data from serialised form in the database.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        if (!is_array($data)) {
            return array();
        }
        $values = $this->field->getValues();
        if (!$values) {
            return $data;
        }
        // Ensure that selected values are still valid for the field.
        foreach ($data as $key => $value) {
            if (!isset($values[ $value ])) {
                unset($data[ $key ]);
            }
        }

        return $data;
    }
}
