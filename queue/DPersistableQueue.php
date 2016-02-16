<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\queue;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DUser;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\DPersistableDefinition;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DLinkedObjectField;
use app\decibel\model\index\DPrimaryIndex;
use app\decibel\regional\DLabel;
use app\decibel\server\DServer;
use app\decibel\utility\DResult;

/**
 * Defines the base class for queueable records.
 *
 * @author    Timothy de Paris
 */
abstract class DPersistableQueue extends DPersistableDefinition
{
    /**
     * 'Created By' field name.
     *
     * @var        string
     */
    const FIELD_CREATED_BY = 'createdBy';

    /**
     * 'Id' field name.
     *
     * @var        string
     */
    const FIELD_ID = 'id';

    /**
     * 'Process ID' field name.
     *
     * @var        string
     */
    const FIELD_PROCESS_ID = 'processId';

    /**
     * Pushes an item onto the queue.
     *
     * @param    array $data Key/value data pairs.
     *
     * @return    DResult
     * @throws    DInvalidPropertyException    If no field exists with this name.
     * @throws    DInvalidFieldValueException    If an invalid value for the field
     *                                        is provided.
     */
    public static function enqueue(array $data)
    {
        // Create a new queueable instance.
        $queueable = static::load(get_called_class());
        // Store the data against the queue.
        foreach ($data as $fieldName => $value) {
            $queueable->setFieldValue($fieldName, $value);
        }
        $user = DAuthorisationManager::getUser();

        return $queueable->save($user);
    }

    /**
     * Returns an item from the queue.
     *
     * @note
     * The returned item will be marked with the current process ID.
     * Marked items will not be returned by subsequent calls to this method,
     * and must be either deleted or returned to the queue using
     * the {@link DPersistableQueue::delete()}
     * or {@link DPersistableQueue::returnToQueue()}
     * methods.
     *
     * @param    array $filter Optional filter to limit returned queue items by.
     *
     * @return    DPersistableQueue    The next item from the queue,
     *                                or <code>null</code> if there are no further
     *                                items in the queue.
     */
    public static function next(array $filter = array())
    {
        $search = static::search()
                        ->filterByField(self::FIELD_PROCESS_ID, null)
                        ->limitTo(1);
        // Apply filter, if provided.
        foreach ($filter as $fieldName => $value) {
            $search->filterByField($fieldName, $value);
        }
        $user = DAuthorisationManager::getUser();
        $item = $search->getObject();
        if ($item !== null) {
            $server = DServer::load();
            $pid = $server->getProcessId();
            $item->setFieldValue(self::FIELD_PROCESS_ID, $pid);
            $item->save($user);
        }

        return $item;
    }

    /**
     * Constructs an instance of this queueable record.
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DPersistableQueue
     */
    final protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $labelNone = new DLabel('app\\decibel', 'none');
        // Add fields to this definiton
        $id = new DIntegerField(self::FIELD_ID, new DLabel(self::class, self::FIELD_ID));
        $id->setUnsigned(true);
        $id->setAutoincrement(true);
        $id->setSize(8);
        $this->addField($id);
        $createdBy = new DLinkedObjectField(self::FIELD_CREATED_BY,
                                            new DLabel(self::class, self::FIELD_CREATED_BY));
        $createdBy->setLinkTo(DUser::class);
        $createdBy->setRelationalIntegrity(DLinkedObjectField::RELATIONAL_INTEGRITY_NONE);
        $createdBy->setReadOnly(true);
        $this->addField($createdBy);
        $processId = new DIntegerField(self::FIELD_PROCESS_ID,
                                       new DLabel(self::class, self::FIELD_PROCESS_ID));
        $processId->setNullOption($labelNone);
        $this->addField($processId);
        // Add primary key index.
        $index = new DPrimaryIndex();
        $index->addField($id);
        $this->addIndex($index);
        // Include custom fields for the extending record type.
        $this->define();
    }

    /**
     * Deletes the class instance from the database.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    public function delete(DUser $user)
    {
        $displayName = static::getDisplayName();
        $result = new DResult($displayName, new DLabel('app\\decibel', 'deleted'));

        return static::executeQuery(
            $result,
            "DELETE FROM {$this->tableName} WHERE `id`=#id# LIMIT 1",
            array(
                self::FIELD_ID => $this->getFieldValue(self::FIELD_ID),
            )
        );
    }

    /**
     * Returns the unique ID for this model instance.
     *
     * @return    int
     */
    public function getId()
    {
        return $this->getFieldValue(self::FIELD_ID);
    }

    /**
     * Builds the SQL query required to save an audit record of this type.
     *
     * @return    string
     */
    protected function getSaveSql()
    {
        $fieldNames = array_diff(
            array_keys($this->fields),
            array(self::FIELD_ID)
        );
        $sqlFields = array();
        foreach ($fieldNames as $fieldName) {
            $sqlFields[] = "`{$fieldName}`='#{$fieldName}#'";
        }

        return "INSERT INTO {$this->tableName} SET " . implode(', ', $sqlFields);
    }

    /**
     * Returns this item to the queue.
     *
     * @return    DResult
     */
    public function returnToQueue()
    {
        $result = new DResult(self::getDisplayName(), 'returned to queue');
        $user = DAuthorisationManager::getUser();
        $this->setFieldValue(self::FIELD_PROCESS_ID, null);
        $result->merge($this->save($user));

        return $result;
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
        $this->setFieldValue(self::FIELD_CREATED_BY, DAuthorisationManager::getResponsibleUser());
    }

    /**
     * Returns a search object that may be used to query this type of record.
     *
     * @return    DPersistableQueueSearch
     */
    public static function search()
    {
        return new DPersistableQueueSearch(get_called_class());
    }
}
