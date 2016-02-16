<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\authorise\DUser;
use app\decibel\cache\DCacheHandler;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\debug\DDebuggable;
use app\decibel\debug\DErrorHandler;
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
use app\decibel\model\field\DField;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\model\search\DLightModelSearch;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DResult;

/**
 * Defines the base class for light models.
 *
 * @author    Timothy de Paris
 */
abstract class DLightModel extends DBaseModel implements DDebuggable
{
    /**
     * 'String Value' field name.
     *
     * @var        string
     */
    const FIELD_STRING_VALUE = 'stringValue';

    /**
     * Creates a light model.
     *
     * This function can be called in a variety of ways:
     * - <code>[Qualified Name]::%create()</code> - Creates a new object of a specific type. Equivalent to passing '0'
     * as the ID parameter.
     * - <code>[Qualified Name]::%create([ID])</code> - Load a specific type of model by ID.
     *
     * @param    int $id          The ID of the model instance to load.
     *                            If omitted, a new model instance will be returned.
     *
     * @return    DLightModel    The loaded model instance, or <code>null</code>
     *                        if no model instance could be found.
     * @throws    DUnknownModelInstanceException            If an invalid model instance ID is provided.
     * @throws    DAbstractModelInstantiationException    If an attempt is made to load an abstract
     *                                                    model class.
     */
    public static function create($id = 0)
    {
        $qualifiedName = get_called_class();
        // Check if we are trying to instantiate an abstract model class.
        $isAbstract = DClassQuery::load()
                                 ->isAbstract($qualifiedName);
        if ($isAbstract) {
            throw new DAbstractModelInstantiationException($qualifiedName);
        }
        // Load object.
        $model = new $qualifiedName($id);
        $model->__wakeup();

        return $model;
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
        // Clear cached model search lists.
        DBaseModelSearch::uncacheModel(get_class($this));
    }

    /**
     * Deletes the model instance from the database.
     *
     * The following checks are performed before deletion may take place:
     * - The {@link DModel::canDelete()} method is called. If this returns
     *        an unsuccessful result, deletion will fail.
     *
     * The following actions are performed following deletion of the model:
     * - The {@link DLightModel::ON_DELETE} event is triggered for this model.
     * - The {@link DLightModel::uncache()} method will be called.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    final public function delete(DUser $user)
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
            // Trigger onDelete event.
            $event = new DOnDelete();
            $this->triggerEvent($event);
            // Finally, uncache the object
            DCacheHandler::startBuffering();
            $this->uncache($event);
            DCacheHandler::stopBuffering();
        }

        // Return the result.
        return $result;
    }

    /**
     * Calculates the string representation of this model.
     *
     * @return    string
     */
    abstract protected function getStringValue();

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
     * Loads the data for the object if it is not a new object.
     *
     * This function must be called by the objects constructor,
     * after setting field information.
     *
     * @param    int  $id         The ID of the object to load. Passing an ID
     *                            of <code>0</code> will create a new object.
     * @param    bool $reload     Whether to force a reload of the object.
     *
     * @return    void
     * @throws    DUnknownModelInstanceException    If an invalid model instance ID is provided.
     */
    protected function loadFromDatabase($id, $reload = false)
    {
        if (!$reload) {
            $this->fieldValues = array();
            $this->originalValues = array();
            $this->fieldPointers = array();
        }
        // Update object ID.
        $this->id = $this->fieldValues['id'] = $id;
        // Load object from database if a non-empty id was given.
        $data = array();
        if ($this->id) {
            $mapper = DDatabaseMapper::decorate($this);
            $sql = $mapper->getLoadSql();
            $query = new DQuery(
                $sql,
                array(
                    'id'        => $this->id,
                    'tableName' => $mapper->getTableName(),
                ),
                $this->getDatabase()
            );
            if ($query->getNumRows()) {
                $data = $query->getNextRow();
            } else {
                throw new DUnknownModelInstanceException($id, get_class($this));
            }
            // Go through all fields and set a value.
            foreach ($this->getFields() as $field) {
                /* @var $field DField */
                $this->loadFieldFromDatabase($field, $data);
            }
        }
        // Trigger onLoad event.
        $event = new DOnLoad();
        $this->triggerEvent($event);
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
        // Check data hasn't already been updated internally.
        // If so, don't override it.
        if (isset($this->originalValues[ $fieldName ])) {
            return;
        }
        // Check if data was in the database.
        if (isset($data[ $fieldName ])) {
            $this->fieldValues[ $fieldName ] = $data[ $fieldName ];
            // Otherwise load the default value for new objects.
        } else {
            if ($this->id === 0) {
                $defaultValue = $field->getDefaultValue();
                // Handle default value set by a method.
                if (is_string($defaultValue)
                    && method_exists($this->definition, $defaultValue)
                ) {
                    $defaultValue = call_user_func(array($this->definition, $defaultValue), array($this));
                }
                // Set default data value.
                $this->fieldValues[ $fieldName ] = $defaultValue;
            } else {
                $this->fieldValues[ $fieldName ] = null;
            }
        }
    }

    /**
     * Saves the object to the database, subject to a variety of checks.
     *
     * @param    DUser $user The user attempting to save the model instance.
     *
     * @return    DResult
     * @throws    DInvalidPropertyExceptionO    If a provided field name is not
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
        // Set up a result object for this action.
        $result = new DResult($this->displayName, 'saved');
        // Remember id before save (to detect new objects).
        $originalId = $this->id;
        // Prepare fields to be saved.
        $this->savePrepare();
        // Check if object can be saved.
        $result->merge($this->canSave($user));
        if (!$result->isSuccessful()) {
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
                DErrorHandler::logException($exception);

                return $result->setSuccess(false, $exception->getMessage());
            }
            // Set id for new objects.
            if ($this->id === 0) {
                $this->id = $this->fieldValues['id'] = $saveQuery->getInsertId();
            }
        }
        // Force wakeup to update any changed object references.
        $this->__wakeup();
        // Trigger post-save events.
        if ($originalId === 0) {
            $firstSaveEvent = new DOnFirstSave();
            $result->merge($this->triggerEvent($firstSaveEvent));
        }
        $saveEvent = new DOnSave();
        $result->merge($this->triggerEvent($saveEvent));
        // Finally uncache the object if required.
        if (count($this->originalValues) > 0) {
            DCacheHandler::startBuffering();
            $this->uncache($saveEvent);
            DCacheHandler::stopBuffering();
        }
        // Clear original data array.
        $this->originalValues = array();

        // Return successful result.
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
            $this->setFieldValue(self::FIELD_STRING_VALUE, $this->getStringValue());
        }
    }

    /**
     * Returns a {@link app::decibel::model::search::DLightModelSearch DLightModelSearch}
     * for this model.
     *
     * @return    DLightModelSearch
     */
    public static function search()
    {
        return new DLightModelSearch(get_called_class());
    }

    /**
     * Returns a {@link app::decibel::model::search::DLightModelSearch DLightModelSearch}
     * that can be used to generate the list of available objects for linking
     * to by {@link app::decibel::model::field::DRelationalField DRelationalField}
     * fields.
     *
     * @param    array $options Additional options for the search.
     *
     * @return    DLightModelSearch
     */
    public static function link($options = array())
    {
        return static::search()
                     ->sortByField('id')
                     ->includeField('id');
    }
}
