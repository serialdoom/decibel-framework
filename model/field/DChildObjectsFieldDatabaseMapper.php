<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\database\debug\DDatabaseException;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\DChild;
use app\decibel\utility\DPersistable;

/**
 * Provides functionality to map a {@link DChildObjectsField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DChildObjectsFieldDatabaseMapper extends DOneToManyRelationalFieldDatabaseMapper
{
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
        $user = DAuthorisationManager::getUser();
        $fieldName = $this->fieldName;
        foreach ($instance->getFieldValue($fieldName) as $childObject) {
            /* @var $childObject DChild */
            $childObject->delete($user);
        }

        return true;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DChildObjectsField::class;
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
        $sql = "GROUP_CONCAT(DISTINCT `decibel_model_field_dchildobjectsfield_{$fieldName}{$tableSuffix}`.`id` SEPARATOR ',')";
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
            $children = $this->field->getSearch()
                                    ->removeDefaultFilters()
                                    ->filterByField(DChild::FIELD_PARENT, $instance->getId())
                                    ->includeFields('id')
                                    ->getFields();
            foreach ($children as $child) {
                $data[ $fieldName ][] = $child['id'];
            }
            // Where no data was loaded, populate with the default value.
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
     * Prepares field data for saving to the database.
     *
     * @param    mixed $data The data to serialize.
     *
     * @return    mixed    The serialized data.
     */
    public function serialize($data)
    {
        if (!is_array($data)) {
            $data = array();
        }
        // Data array could contain IDs or DChild instances.
        foreach ($data as $position => $child) {
            // If no changes were made, just store the ID.
            if (is_object($child)
                && !$child->hasUnsavedChanges()
            ) {
                $data[ $position ] = $child->getId();
            }
        }

        return $data;
    }

    /**
     * Restores data from its serialised form in the database.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        if (is_array($data)) {
            $unserialized = array();
            foreach ($data as $value) {
                if (is_numeric($value)) {
                    $value = DChild::create((int)$value);
                }
                if ($value) {
                    $unserialized[] = $value;
                }
            }
        } else {
            $unserialized = array();
        }

        return $unserialized;
    }
}
