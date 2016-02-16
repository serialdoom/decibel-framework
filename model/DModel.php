<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DUser;
use app\decibel\cache\DCacheHandler;
use app\decibel\cache\DModelCache;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DFullProfiler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DProfiler;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DAbstractModelInstantiationException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\debug\DRecursiveModelSaveException;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\event\DModelEvent;
use app\decibel\model\event\DOnDelete;
use app\decibel\model\event\DOnFirstSave;
use app\decibel\model\event\DOnLoad;
use app\decibel\model\event\DOnSave;
use app\decibel\model\event\DOnSubsequentSave;
use app\decibel\model\event\DOnUncache;
use app\decibel\model\field\DField;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\model\search\DModelSearch;
use app\decibel\model\utilisation\DUtilisationRecord;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DResult;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Provides a base for models within a %Decibel application.
 *
 * @author         Timothy de Paris
 * @ingroup        models
 */
abstract class DModel extends DBaseModel implements DDebuggable, DProcessCacheable
{
    use DProcessCacheableModel;
    use DRelationalModel;

    /**
     * 'Created' field name.
     *
     * @var        string
     */
    const FIELD_CREATED = 'created';

    /**
     * 'Created By' field name.
     *
     * @var        string
     */
    const FIELD_CREATED_BY = 'object_createdBy';

    /**
     * 'GUID' field name.
     *
     * @var        string
     */
    const FIELD_GUID = 'guid';

    /**
     * 'Last Updated' field name.
     *
     * @var        string
     */
    const FIELD_LAST_UPDATED = 'lastUpdated';

    /**
     * 'Last Updated By' field name.
     *
     * @var        string
     */
    const FIELD_LAST_UPDATED_BY = 'object_updatedBy';

    /**
     * 'String Value' field name.
     *
     * @var        string
     */
    const FIELD_STRING_VALUE = 'stringValue';

    /**
     * Creates a model instance, utilising caching.
     *
     * This function can be called in a variety of ways:
     * - <code>[Qualified Name]::%create()</code> - Creates a new object of a specific type. Equivalent to passing '0'
     * as the ID parameter.
     * - <code>[Qualified Name]::%create([ID])</code> - Load a specific type of object by ID. Must be passed as an
     * integer.
     * - <code>app\\decibel\\model\\DModel::create([ID])</code> - Load any type of object by UID. Must be passed as an
     * integer.
     *
     * @param    int $id                  The ID of the model instance to load.
     *                                    If omitted, a new model instance will be returned.
     *
     * @return    static    The model instance.
     * @throws    DUnknownModelInstanceException            If an invalid model instance ID is provided.
     * @throws    DAbstractModelInstantiationException    If an attempt is made to load an abstract
     *                                                    model class.
     */
    public static function create($id = 0)
    {
        // Check the class exists.
        $qualifiedName = static::getQualifiedNameForId($id);
        if ($qualifiedName === null
            || !class_exists($qualifiedName)
        ) {
            throw new DUnknownModelInstanceException($id, $qualifiedName);
        }
        // Check locally cached models.
        $instance = self::retrieveFromProcessCache($id);
        if ($instance !== null) {
            if (defined(DProfiler::PROFILER_ENABLED)) {
                $profiler = DFullProfiler::load();
                $profiler->trackObjectLoad(DFullProfiler::MODEL_LOAD_MEMORY);
            }

            return $instance;
        }
        // Check for this object in the cache.
        $modelCache = DModelCache::load();
        $model = $modelCache->retrieve($id, $qualifiedName);
        // Load object and check it is of the specified type.
        if ($model === null) {
            $model = new $qualifiedName($id);
            $model->__wakeup();
            // Track the load.
            if (defined(DProfiler::PROFILER_ENABLED)) {
                $profiler = DFullProfiler::load();
                $profiler->trackObjectLoad(DFullProfiler::MODEL_LOAD_DATABASE);
            }
        }
        self::cacheInProcess($model);

        return $model;
    }

    /**
     * Determines the qualified name for the provided ID.
     *
     * @param    int $id
     *
     * @return    string
     * @throws    DAbstractModelInstantiationException    If an attempt is made to
     *                                                    load an abstract model class.
     */
    private static function getQualifiedNameForId($id)
    {
        $id = (int)$id;
        $qualifiedName = get_called_class();
        $modelCache = DModelCache::load();
        // Check if we are trying to instantiate an abstract model class.
        $isAbstract = DClassQuery::load()
                                 ->isAbstract($qualifiedName);
        if ($isAbstract) {
            // Can't create a new instance of an abstract class.
            if ($id === 0) {
                throw new DAbstractModelInstantiationException($qualifiedName);
            }
            // Otherwise see if it is an existing model being instantiated
            // using a parent class name.
            $qualifiedName = $modelCache->getQualifiedNameForId($id);
        } else {
            if ($id !== 0) {
                $qualifiedNameCheck = $modelCache->getQualifiedNameForId($id);
                if ($qualifiedNameCheck === null) {
                    $qualifiedName = null;
                } else {
                    if ($qualifiedName !== $qualifiedNameCheck) {
                        // We can still load if the actual model
                        // is a child of the requested model.
                        try {
                            $reflection = new ReflectionClass($qualifiedNameCheck);
                            if ($reflection->isSubclassOf($qualifiedName)) {
                                $qualifiedName = $qualifiedNameCheck;
                            } else {
                                $qualfiedName = null;
                            }
                            // Catch exception thrown if this is an invalid class name.
                        } catch (ReflectionException $e) {
                            $qualfiedName = null;
                        }
                    }
                }
            } else {
                // The qualified name is that of the class
                // the method was called against.
            }
        }

        return $qualifiedName;
    }

    /**
     * Returns an array of cacheable fields. Object references should not
     * be cached to maintain integrity.
     *
     * @return    void
     */
    public function __sleep()
    {
        return array(
            'id',
            'fieldValues',
            'displayName',
            'displayNamePlural',
            'tableName',
            self::FIELD_QUALIFIED_NAME,
        );
    }

    /**
     * Returns the string representation of this model.
     *
     * This function returns the value of the calculated field
     * <code>stringValue</code>, which calculates the string representation using the
     * {@link app::decibel::model::DBaseModel::getStringValue() DBaseModel::getStringValue()}
     * method.
     *
     * @return    string
     */
    final public function __toString()
    {
        $stringValue = $this->getFieldValue(self::FIELD_STRING_VALUE);
        if (count($this->originalValues) > 0
            || $stringValue === ''
        ) {
            try {
                $stringValue = $this->getStringValue();
            } catch (Exception $e) {
                DErrorHandler::throwException($e);
            }
        }

        return $stringValue;
    }

    /**
     * Performs any uncaching operations neccessary when a model's data is changed to ensure
     * consitency across the application.
     *
     * @param    DModelEvent $event The event that required uncaching of the model.
     *
     * @return    void
     */
    public function uncache(DModelEvent $event = null)
    {
        if ($this->originalValues
            || $event == self::ON_DELETE
        ) {
            // Remove data from caches.
            $modelCache = DModelCache::load();
            $modelCache->removeModelInstance($this->id, get_class($this));
            // Clear cached model search lists.
            DBaseModelSearch::uncacheModel(get_class($this));
        }
        // Uncache any objects that link to this object.
        foreach (DUtilisationRecord::getUtilisingIds($this) as $id) {
            if (!isset(self::$uncached[ $id ])) {
                self::$uncached[ $id ] = true;
                try {
                    self::create($id)
                        ->uncache($event);
                } catch (DUnknownModelInstanceException $exception) {
                }
            }
        }
        $uncacheEvent = new DOnUncache();
        $this->triggerEvent($uncacheEvent);
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        $events = parent::getEvents();
        $events[] = self::ON_UNCACHE;

        return $events;
    }

    /**
     * Retrieves the value for a specified field.
     *
     * @param    string $fieldName Name of the field to get the value for.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the specified field does not exist.
     */
    public function getFieldValue($fieldName)
    {
        $field = $this->getField($fieldName);
        // Check if field needs to be unserialized.
        if (!isset($this->fieldPointers[ $fieldName ])) {
            $mapper = $field->getDatabaseMapper();
            $value = $mapper->unserialize($this->fieldValues[ $fieldName ]);
            $this->fieldPointers[ $fieldName ] =& $value;
        }

        // Return field value.
        return $this->fieldPointers[ $fieldName ];
    }

    /**
     * Returns a global unique identifier for this model instance.
     *
     * @return    string    The 32-character GUID for this model instance.
     */
    public function getGuid()
    {
        return $this->getFieldValue(self::FIELD_GUID);
    }

    /**
     * Loads the ID of the model instance with the specified GUID.
     *
     * @param    string $guid GUID to convert to an ID.
     *
     * @return    int        The model instance ID, or <code>null</code> if no model
     *                    instance could be found.
     */
    public static function getIdForGuid($guid)
    {
        return self::search()
                   ->removeDefaultFilters()
                   ->filterByField(self::FIELD_GUID, $guid)
                   ->limitTo(1)
                   ->getId();
    }

    /**
     * Retrieves the value for a specified field, in serialized format.
     *
     * @param    string $fieldName Name of the field to get the value for.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the specified field does not exist.
     */
    public function getSerializedFieldValue($fieldName)
    {
        // Throw an exception if the requested field does not exist.
        $this->getField($fieldName);

        // Return field value.
        return $this->fieldValues[ $fieldName ];
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    abstract protected function getStringValue();

    /**
     * Returns the owner of this model instance.
     *
     * @return    DUser    The owner, or <code>null</code> if this is no owner.
     */
    public function getOwner()
    {
        $createdBy = $this->getFieldValue(self::FIELD_CREATED_BY);
        $updatedBy = $this->getFieldValue(self::FIELD_LAST_UPDATED_BY);
        // Find out the group's 'owner'.
        if ($createdBy) {
            $owner = $createdBy;
            // Fall back to the last updated by if the creator no longer exists.
        } else {
            if ($updatedBy) {
                $owner = $updatedBy;
                // Can't continue if the updater doesn't exist either.
            } else {
                $owner = null;
            }
        }

        return $owner;
    }

    /**
     * Deletes the model instance from the database.
     *
     * The following checks are performed before deletion may take place:
     * - The {@link DModel::canDelete()} method is called. If this returns
     *        an unsuccessful result, deletion will fail.
     *
     * The following actions are performed following deletion of the model:
     * - The {@link DBaseModel::ON_DELETE} event is triggered for this model.
     * - The {@link DBaseModel::uncache()} method will be called.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    public function delete(DUser $user)
    {
        // Can't delete a new object.
        if ($this->id === 0) {
            return null;
        }
        // Check if object can be deleted.
        $result = $this->canDelete($user);
        // Don't need to do anything else if it can't be deleted.
        if ($result->isSuccessful()) {
            // Delete the object from the database.
            $mapper = DDatabaseMapper::decorate($this);
            $result->merge($mapper->delete());
            // Delete non-native fields.
            foreach ($this->getFields() as $field) {
                /* @var $field DField */
                $field->getDatabaseMapper()->delete($this);
            }
            // Delete any references to this object as a linked child object.
            new DQuery('App_LinkedObject_deleteTo', array(
                'to' => $this->id,
            ));
            // Trigger onDelete event.
            $event = new DOnDelete();
            $this->triggerEvent($event);
            // Finally uncache the object
            DCacheHandler::startBuffering();
            $this->uncache($event);
            DCacheHandler::stopBuffering();
        }

        // Return successful result.
        return $result;
    }

    /**
     * Returns the name of the field that contains a string representation
     * of this model instance.
     *
     * @return    string
     */
    public static function getStringValueFieldName()
    {
        return self::FIELD_STRING_VALUE;
    }

    /**
     * Loads the data for the object if it is not a new object. This function must be called
     * by the objects constructor, after setting field information.
     *
     * @param    int  $id         The id of the object to load. Passing an id of
     *                            0 will create a new object.
     * @param    bool $reload     Whether to force a reload of the object.
     *
     * @return    void
     * @throws    DUnknownModelInstanceException    If an invalid model instance ID is provided.
     */
    protected function loadFromDatabase($id, $reload = false)
    {
        if (!$reload) {
            $this->originalValues = array();
        }
        // Update object ID.
        $this->id = $this->fieldValues['id'] = (int)$id;
        // Load object from database if a non-empty id was given, otherwise
        // obtain array of field names for the object.
        if ($this->id) {
            $mapper = DDatabaseMapper::decorate($this);
            $sql = $mapper->getLoadSql();
            $query = new DQuery($sql, array(
                'id'        => $this->id,
                'tableName' => $mapper->getTableName(),
            ));
            if ($query->getNumRows()) {
                // The query will return native data which will be used below.
                $data = $query->getNextRow();
                // If the database contains records for this model, clear any existing
                // data (mostly if this has been reloaded). Data won't be cleared if no
                // records are found in the database, as we may be reloading a deleted
                // object and we don't want to lose all the data just now.
                $this->fieldPointers = array();
                $this->fieldValues = array();
            } else {
                throw new DUnknownModelInstanceException($id, get_class($this));
            }
            // Load data for each of the object's fields.
            foreach ($this->getFields() as $field) {
                /* @var $field DField */
                $this->loadFieldFromDatabase($field, $data);
            }
        }
        // Load default values for fields.
        $this->loadDefaultValues();
        // Perform post cache retrieval functions.
        $this->__wakeup();
        // Cache the object.
        DModelCache::load()->set($this);
    }

    /**
     * Stores loaded data for the specified field.
     *
     * @param    DField $field
     * @param    array  $data
     *
     * @return    void
     */
    protected function loadFieldFromDatabase(DField $field, array &$data)
    {
        $fieldName = $field->getName();
        // Don't do anything if the value has already been changed.
        if (!isset($this->originalValues[ $fieldName ])) {
            // Load other non-native fields.
            if (!$field->isNativeField()) {
                $field->getDatabaseMapper()->load($this, $this->fieldValues);
                // If native data was loaded from the database, serialize it.
            } else {
                if (isset($data[ $fieldName ])) {
                    $this->fieldValues[ $fieldName ] = $field->serialize($data[ $fieldName ]);
                }
            }
        }
    }

    /**
     * Loads default values for the fields in this object.
     *
     * @param    bool $force      If set to true, function based default values
     *                            will override existing field values. If omitted,
     *                            default values will only be loaded where data
     *                            does not already exist.
     *
     * @return    void
     */
    public function loadDefaultValues($force = false)
    {
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            // Determine if default value should be set for this field.
            if (!$force && array_key_exists($fieldName, $this->fieldValues)) {
                continue;
            }
            // Determine default value for field.
            $this->fieldValues[ $fieldName ] = $field->getDefaultValue();
        }
        // Trigger onLoad event.
        // Moved here from DModel::load as when a new instance (i.e. ID = 0)
        // is retrieved from the cache loadDefaultValues is called which may
        // override a field value previously set by an onLoad handler.
        $event = new DOnLoad();
        $this->triggerEvent($event);
    }

    /**
     * Saves the object to the database, subject to a variety of checks.
     *
     * @param    DUser $user The user attempting to save the model instance.
     *
     * @return    DResult
     * @throws    DInvalidPropertyException    If a provided field name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for a field cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If a provided field value
     *                                        is not valid for the field.
     * @throws    DRecursiveModelSaveException    If a recursive save action is
     *                                            detected on this model instance.
     */
    public function save(DUser $user)
    {
        // Don't do anything if save has already been called 3 times,
        // this is almost definitely recursion!
        if ($this->saveCount > 3) {
            throw new DRecursiveModelSaveException(get_class($this), $this->id);
        }
        ++$this->saveCount;
        // Set up a result object for this action.
        $result = new DResult($this->displayName, 'saved');
        // Remember id before save (to detect new objects).
        $originalId = $this->id;
        // Prepare fields to be saved.
        $this->savePrepare();
        // Check if object can be saved.
        $result->merge($this->canSave($user));
        if (!$result->isSuccessful()) {
            --$this->saveCount;

            return $result;
        }
        // Create and execute the query sql if required.
        $mapper = DDatabaseMapper::decorate($this);
        $changedFields = array_keys($this->originalValues);
        $sql = $mapper->getSaveSql($changedFields);
        if ($sql) {
            // Save to database.
            try {
                $saveQuery = new DQuery(
                    $sql,
                    $mapper->getSerialisedData(),
                    $this->getDatabase()
                );
            } catch (DQueryExecutionException $exception) {
                DErrorHandler::throwException($exception);

                return $result->setSuccess(false, $exception->getMessage());
            }
            // Set id for new objects.
            if ($this->id === 0) {
                $this->id = $this->fieldValues['id'] = $this->fieldPointers['id'] = $saveQuery->getInsertId();
                $this->fieldPointers[ self::FIELD_GUID ] = $this->fieldValues[ self::FIELD_GUID ] = md5(get_class($this) . $this->id);
            }
        }
        // Save data for non-native fields.
        foreach ($changedFields as $fieldName) {
            /* @var $field DField */
            $field = $this->getField($fieldName);
            if (!$field->isNativeField()) {
                $field->getDatabaseMapper()->save($this);
            }
        }
        // Force wakeup to update any changed object references.
        $this->__wakeup();
        // Trigger post-save events.
        if ($originalId === 0) {
            $firstSaveEvent = new DOnFirstSave();
            $result->merge($this->triggerEvent($firstSaveEvent));
        } else {
            $subsequentSaveEvent = new DOnSubsequentSave();
            $result->merge($this->triggerEvent($subsequentSaveEvent));
        }
        $saveEvent = new DOnSave();
        $result->merge($this->triggerEvent($saveEvent));
        // Finally uncache the object if required.
        if (count($this->originalValues) > 0) {
            DCacheHandler::startBuffering();
            $this->uncache($saveEvent);
            DCacheHandler::stopBuffering();
        }
        // If this is a new model, put it into the cache to ensure
        // any create operations that occur after this point in the
        // same script return this instance.
        if ($originalId == 0) {
            self::cacheInProcess($this);
        }
        // Clear original data array.
        $this->originalValues = array();
        // Return successful result.
        --$this->saveCount;

        return $result;
    }

    /**
     * Prepares fields to be saved by applying any updated data.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If a provided field name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for a field cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If a provided field value
     *                                        is not valid for the field.
     */
    protected function savePrepare()
    {
        parent::savePrepare();
        if ($this->hasUnsavedChanges()) {
            $responsibleUser = DAuthorisationManager::getResponsibleUser();
            $this->setFieldValue(self::FIELD_LAST_UPDATED_BY, $responsibleUser);
            $this->setFieldValue(self::FIELD_LAST_UPDATED, time());
            $this->setFieldValue(self::FIELD_STRING_VALUE, $this->getStringValue());
        }
    }

    /**
     * Sets default field values for new model instances.
     *
     * @return    void
     */
    protected function setDefaultValues()
    {
        $this->setFieldValue(self::FIELD_CREATED, time());
        $this->setFieldValue(self::FIELD_CREATED_BY, DAuthorisationManager::getUser());
    }

    /**
     * Ensures no extraneous records exist in the object tables
     * within the database.
     *
     * Called on the <code>app\\decibel\\database\\DDatabase-optimise</code> event.
     *
     * @return    void
     */
    public static function cleanDatabase()
    {
        // Remove any record from a model table that does not have
        // a corresponding record in the dmodel table.
        $abstractModels = DClassQuery::load()
                                     ->setAncestor(DModel::class)
                                     ->setFilter(DClassQuery::FILTER_ABSTRACT)
                                     ->getClassNames();
        $abstractModels[] = self::class;
        foreach ($abstractModels as $qualifiedName) {
            try {
                new DQuery('app\\decibel\\model\\DModel-cleanMissingModels', array(
                    'qualifiedName' => $qualifiedName,
                    'table'         => DDatabaseMapper::getTableNameFor($qualifiedName),
                ));
            } catch (DQueryExecutionException $e) {
            }
        }
        // Call cleaning functionality in available field database mappers.
        $mappers = DClassQuery::load()
                              ->setAncestor('app\\decibel\\model\\field\\DFieldDatabaseMapper')
                              ->getClassNames();
        foreach ($mappers as $mapper) {
            $mapper::cleanDatabase();
        }
    }

    /**
     * Returns a {@link app::decibel::model::search::DModelSearch DModelSearch} for this model.
     *
     * @return    DModelSearch
     */
    public static function search()
    {
        return new DModelSearch(get_called_class());
    }

    /**
     * Returns a {@link app::decibel::model::search::DModelSearch DModelSearch}
     * that can be used to generate the list of available objects for linking
     * to by {@link app::decibel::model::field::DRelationalField DRelationalField}
     * fields.
     *
     * @param    array $options Additional options for the search.
     *
     * @return    DModelSearch
     */
    public static function link($options = array())
    {
        return static::search()
                     ->sortByField(self::FIELD_STRING_VALUE)
                     ->includeFields(array(
                                         self::FIELD_STRING_VALUE,
                                     ));
    }
}
