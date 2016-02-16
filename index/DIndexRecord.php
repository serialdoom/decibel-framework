<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\index;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DUser;
use app\decibel\database\DDatabase;
use app\decibel\database\DDatabaseInformation;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DNotImplementedException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\index\DIndexRecord;
use app\decibel\index\DIndexSearch;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\DPersistableDefinition;
use app\decibel\regional\DLabel;
use app\decibel\utility\DResult;

/**
 * Defines the base class for index records.
 *
 * @author    Timothy de Paris
 */
abstract class DIndexRecord extends DPersistableDefinition
{
    /**
     * Constructs an instance of this index record.
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DIndexRecord
     */
    protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        // Include custom fields for the extending record type.
        $this->define();
    }

    /**
     * Creates a new {@link DIndexRecord} instance.
     *
     * @param    array $data The data to include in the index.
     *
     * @return    DIndexRecord
     * @throws    DInvalidPropertyException    If a provided field name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for a field cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for a field.
     */
    public static function create(array $data)
    {
        $record = new static(get_called_class());
        foreach ($data as $fieldName => $value) {
            $record->setFieldValue($fieldName, $value);
        }

        return $record;
    }

    /**
     * Deletes the class instance from the database.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     * @todo    Implement this.
     */
    final public function delete(DUser $user)
    {
        return new DResult(
            static::getDisplayName(),
            new DLabel('app\\decibel', 'deleted'),
            false
        );
    }

    /**
     * Returns the number of records currently stored within the index.
     *
     * This is not neccessarily the number of rows in the database table
     * for this index. It will usually return the number of distinct models
     * that have been included in the index.
     *
     * @return    int
     */
    public static function getCurrentIndexSize()
    {
        return static::search()
                     ->removeDefaultFilters()
                     ->getCount();
    }

    /**
     * Returns the unique ID for this model instance.
     *
     * @return    string
     * @todo    Implement this.
     */
    public function getId()
    {
        return null;
    }

    /**
     * Returns the number of records that would be stored within the index
     * if all indexable models were included.
     *
     * @return    int
     */
    public static function getMaximumIndexSize()
    {
        throw new DNotImplementedException(array(get_called_class(), __FUNCTION__));
    }

    /**
     * Returns the number of records and physical disc space used by the index.
     *
     * An array containing the following keys must be returned:
     * - <strong>size</strong>: Physical size of the index in the database.
     * - <strong>rows</strong>: The number of records in the index.
     *
     * @return    array
     */
    public static function getPhysicalIndexSize()
    {
        // Get information about index database table.
        $database = DDatabase::getDatabase();
        $databaseInformation = DDatabaseInformation::adapt($database);

        return $databaseInformation->getSize(true, DDatabaseMapper::getTableNameFor(get_called_class()));
    }

    /**
     * Determines the status of rebuilding this index.
     *
     * @return    mixed    <code>false</code> if no rebuild has been scheduled,
     *                    the timestamp at which rebuilding is scheduled,
     *                    or <code>true</code> if a rebuild is in progress.
     */
    final public static function getRebuildStatus()
    {
        $eventName = static::getRebuildTaskName();
        if ($eventName) {
            $status = $eventName::getStatus();
        } else {
            $status = null;
        }

        return $status;
    }

    /**
     * Returns the qualified name of the
     * {@link app::decibel::task::DScheduledTask DScheduledTask}
     * that can rebuild this index.
     *
     * @return    string
     */
    public static function getRebuildTaskName()
    {
        throw new DNotImplementedException(array(get_called_class(), __FUNCTION__));
    }

    /**
     * Builds the SQL query required to save an audit record of this type.
     *
     * @return    string
     */
    protected function getSaveSql()
    {
        $fieldNames = array_keys($this->fields);
        $sqlFields = array();
        foreach ($fieldNames as $fieldName) {
            $sqlFields[] = "`{$fieldName}`='#{$fieldName}#'";
        }

        return "REPLACE INTO {$this->tableName} SET " . implode(', ', $sqlFields);
    }

    /**
     * Sets default values for this object before saving.
     *
     * @note
     * This function can be used to override previously set field values
     * if required.
     *
     * @return    void
     */
    protected function setDefaultValues()
    {
        $this->setFieldValue(self::FIELD_CREATED, time());
    }

    /**
     * Updates data in the index.
     *
     * The format of the input array is key => values
     * pairs where the key corresponds to the field name.
     *
     * @param    array $data
     *
     * @return    DResult
     * @throws    DInvalidPropertyException    If no field exists with this name.
     * @throws    DInvalidFieldValueException    If an invalid value for the field
     *                                        is provided.
     */
    public static function update(array $data)
    {
        // Create a new audit record instance.
        $indexRecord = static::load(get_called_class());
        // Store the data against the audit record.
        foreach ($data as $fieldName => $value) {
            $indexRecord->setFieldValue($fieldName, $value);
        }
        $user = DAuthorisationManager::getUser();

        return $indexRecord->save($user);
    }

    /**
     * Returns an DIndexSearch of the current type.
     *
     * @return    DIndexSearch
     */
    public static function search()
    {
        return new DIndexSearch(get_called_class());
    }
}
