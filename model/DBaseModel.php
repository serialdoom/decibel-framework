<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\authorise\debug\DUnprivilegedException;
use app\decibel\authorise\DPrivilege;
use app\decibel\authorise\DUser;
use app\decibel\authorise\DUserPrivileges;
use app\decibel\database\DDatabase;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DNotImplementedException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\event\DDispatchable;
use app\decibel\event\debug\DInvalidEventException;
use app\decibel\event\DEventDispatcher;
use app\decibel\event\DInvalidEventHandlerResultException;
use app\decibel\http\request\DRequest;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\debug\DUnknownModelInstanceException;
use app\decibel\model\event\DModelEvent;
use app\decibel\model\event\DOnBeforeDelete;
use app\decibel\model\event\DOnBeforeFirstSave;
use app\decibel\model\event\DOnBeforeLoad;
use app\decibel\model\event\DOnBeforeSave;
use app\decibel\model\event\DOnDelete;
use app\decibel\model\event\DOnFirstSave;
use app\decibel\model\event\DOnLoad;
use app\decibel\model\event\DOnSave;
use app\decibel\model\event\DOnSubsequentSave;
use app\decibel\model\field\DField;
use app\decibel\model\field\DRelationalField;
use app\decibel\model\index\DIndex;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\model\search\DSearchable;
use app\decibel\reflection\DReflectable;
use app\decibel\regional\DLabel;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DDescribable;
use app\decibel\utility\DDescribableObject;
use app\decibel\utility\DPersistable;
use app\decibel\utility\DResult;
use Exception;
use JsonSerializable;
use stdClass;

/**
 * Defines the base class for models.
 *
 * @author    Timothy de Paris
 */
abstract class DBaseModel implements DAdaptable, DCacheable, DDecoratable, DDefinable,
                                     DDescribable, DDispatchable, DPersistable, DReflectable, DSearchable,
                                     JsonSerializable
{
    use DAdapterCache;
    use DCacheableModel;
    use DDecoratorCache;
    use DDefinableObject;
    use DDescribableObject;
    use DEventDispatcher;

    /**
     * 'Qualified Name' field name.
     *
     * @var        string
     */
    const FIELD_QUALIFIED_NAME = 'qualifiedName';

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnBeforeLoad DOnBeforeLoad} event.
     *
     * @var        string
     */
    const ON_BEFORE_LOAD = DOnBeforeLoad::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnBeforeDelete DOnBeforeDelete} event.
     *
     * @var        string
     */
    const ON_BEFORE_DELETE = DOnBeforeDelete::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnBeforeFirstSave DOnBeforeFirstSave} event.
     *
     * @var        string
     */
    const ON_BEFORE_FIRST_SAVE = DOnBeforeFirstSave::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnBeforeSave DOnBeforeSave} event.
     *
     * @var        string
     */
    const ON_BEFORE_SAVE = DOnBeforeSave::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnDelete DOnDelete} event.
     *
     * @var        string
     */
    const ON_DELETE = DOnDelete::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnFirstSave DOnFirstSave} event.
     *
     * @var        string
     */
    const ON_FIRST_SAVE = DOnFirstSave::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnLoad DOnLoad} event.
     *
     * @var        string
     */
    const ON_LOAD = DOnLoad::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnSave DOnSave} event.
     *
     * @var        string
     */
    const ON_SAVE = DOnSave::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::model::event::DOnSubsequentSave DOnSubsequentSave} event.
     *
     * @var        string
     */
    const ON_SUBSEQUENT_SAVE = DOnSubsequentSave::class;

    /**
     * The human-readable name of this object.
     *
     * @var        string
     */
    protected $displayName;

    /**
     * The human-readable plural name of this object.
     *
     * @var        string
     */
    protected $displayNamePlural;

    /**
     * The unique ID of this object.
     *
     * @var        int
     */
    protected $id;

    ///@cond INTERNAL
    /**
     * Original field values.
     *
     * @var        array
     * @deprecated    In favour of {@link DDefinableObject::$originalValues}
     */
    protected $originalData;

    ///@endcond
    /**
     * Pointer to the definition for this object.
     *
     * @var        DBaseModel_Definition
     */
    protected $definition;

    /**
     * The database in which this model instance is persisted.
     *
     * Extending classes may set this to a foreign database to allow peristance
     * of a light model in a database other than the primary Decibel interface.
     *
     * @var        DDatabase
     */
    protected $database = null;

    /**
     * Tracks the number of times the save method has been recursively
     * called on this model instance.
     *
     * This is used to avoid possible recursion due to badly implemented
     * event handlers.
     *
     * @var        int
     */
    protected $saveCount = 0;

    /**
     * Returns the current database for this model.
     *
     * @return    DDatabase
     */
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = DDatabase::getDatabase();
        }

        return $this->database;
    }

    /**
     * Set the required variables and load the object data from the database.
     * If the id variable is empty, a new object is created, otherwise an
     * existing object is loaded.
     *
     * @note
     * Models must be loaded using the {@link DBaseModel::create()} function.
     *
     * @param    int $id          The ID of the model instance to load.
     *                            If omitted, a new model instance will be returned.
     *
     * @return    static
     * @throws    DUnknownModelInstanceException    If an invalid model instance ID is provided.
     */
    protected function __construct($id = 0)
    {
        // Determine information about this object.
        $qualifiedName = get_called_class();
        $this->displayName = $qualifiedName::getDisplayName();
        $this->displayNamePlural = $qualifiedName::getDisplayNamePlural();
        // Load the definition for this object if required.
        $this->definition = DDefinition::load($qualifiedName);
        // Link the fields from the definition to the DDefinableObject::$fields property
        // so that the functions defined in DDefinableObject trait work correctly.
        $this->fields =& $this->definition->fields;
        $this->originalData =& $this->originalValues;
        // Load the object data.
        $this->load((int)$id);
    }

    ///@cond INTERNAL
    /**
     * Handles retrieval of object parameters.
     *
     * @param    string $name The name of the parameter to retrieve.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the specified property does not exist.
     * @deprecated
     */
    public function __get($name)
    {
        switch ($name) {
            case 'displayName':
            case 'displayNamePlural':
            case 'originalData':
            case 'definition':
                $value = $this->$name;
                break;
            default:
                $value = $this->getFieldValue($name);
                break;
        }

        return $value;
    }
    ///@endcond
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
    public function __toString()
    {
        try {
            return $this->getStringValue();
        } catch (Exception $exception) {
            DErrorHandler::throwException($exception);
        }
    }

    /**
     * Performs post cache retrieval functions.
     *
     * @return    void
     */
    public function __wakeup()
    {
        // Load the definition for this object if required.
        $this->definition = DDefinition::load(get_class($this));
        // Link the fields from the definition to the DDefinableObject::$fields property
        // so that the functions defined in DDefinableObject trait work correctly.
        $this->fields =& $this->definition->fields;
        $this->originalData =& $this->originalValues;
    }

    /**
     * Performs checks to determine if this object can be deleted. Rather than
     * overriding this function, extending objects should register
     * {@link app::decibel::model::DBaseModel::ON_BEFORE_DELETE DBaseModel::ON_BEFORE_DELETE}
     * event handlers.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     * @throws    DUnprivilegedException    If the user does not have the required privilege.
     */
    final public function canDelete(DUser $user)
    {
        // Check that the user is authorised first.
        $result = $this->userCanDelete($user);
        // Trigger ON_BEFORE_DELETE event.
        $event = new DOnBeforeDelete();
        $result->merge(
            $this->triggerEvent($event)
        );

        return $result;
    }

    /**
     * Performs checks to determine if this object can be saved with the provided
     * data updates. Rather than overriding this function, extending objects
     * should register {@link app::decibel::model::DBaseModel::ON_BEFORE_SAVE DBaseModel::ON_BEFORE_SAVE} or
     * {@link app::decibel::model::DBaseModel::ON_BEFORE_FIRST_SAVE DBaseModel::ON_BEFORE_FIRST_SAVE} event handlers.
     *
     * @param    DUser $user The user attempting to save the model instance.
     *
     * @return    DResult
     * @throws    DUnprivilegedException    If the user does not have the required privilege.
     */
    final public function canSave(DUser $user)
    {
        // Check that the user is authorised first.
        $result = $this->userCanSave($user);
        // Trigger pre-save events and then validate data.
        // Order is important as ON_BEFORE_SAVE events may update data
        // so that it passes validation.
        if ($this->id === 0) {
            $firstSaveEvent = new DOnBeforeFirstSave();
            $result->merge($this->triggerEvent($firstSaveEvent));
        }
        $saveEvent = new DOnBeforeSave();
        $result->merge($this->triggerEvent($saveEvent));
        // Validate field values.
        $result->merge($this->validate());

        return $result;
    }

    /**
     * Clears the cached reference to this model allowing associated
     * memory to be freed.
     *
     * @return    void
     */
    public function free()
    {
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    final public static function getDefaultEvent()
    {
        return self::ON_LOAD;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_BEFORE_DELETE,
            self::ON_BEFORE_FIRST_SAVE,
            self::ON_BEFORE_LOAD,
            self::ON_BEFORE_SAVE,
            self::ON_DELETE,
            self::ON_FIRST_SAVE,
            self::ON_LOAD,
            self::ON_SAVE,
            self::ON_SUBSEQUENT_SAVE,
            self::ON_UNCACHE,
        );
    }

    /**
     * Returns the unique ID for this model instance.
     *
     * @return    int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retrieves the value of an option for this object.
     *
     * @param    string $option The option to set.
     *
     * @return    mixed    The value of the option, or null if
     *                    the option has not been set.
     */
    final public function getOption($option)
    {
        return $this->definition->getOption($option);
    }

    /**
     * Returns the name for a privilege for this object with the provided suffix.
     *
     * Privilege names are of the format <code>[Qualified Name]-[suffix]</code>.
     *
     * If the privilege suffix provided is not valid for this object, null
     * will be returned.
     *
     * @note
     * If the 'Create' privilege is requested, and this is not available
     * for this type of model, the 'Edit' privilege will be returned instead.
     *
     * @param    string $suffix   The privilege suffix. A suffix must be comprised
     *                            of an upper case letter followed by one or more
     *                            lower case letters.
     *
     * @return    string
     */
    public function getPrivilegeName($suffix)
    {
        // Check that provided suffix is valid.
        if (!preg_match('/[A-Z][a-z]+/', $suffix)) {
            $name = null;
        } else {
            $qualifiedName = get_called_class();
            $name = "{$qualifiedName}-{$suffix}";
            // Special case for 'Create' privilege.
            if ($suffix === DPrivilege::SUFFIX_CREATE
                && !DPrivilege::isValid($name)
            ) {
                $name = "{$qualifiedName}-" . DPrivilege::SUFFIX_EDIT;
            }
        }

        return $name;
    }

    /**
     * Provides a reflection of this class.
     *
     * @return    DBaseModelReflection
     */
    public static function getReflection()
    {
        return new DBaseModelReflection(get_called_class());
    }

    /**
     * Takes request data submitted from an object administration form
     * and converts it into the format required to be merged with the
     * object's existing data. This should be overriden by objects with
     * non-standard fields to ensure all submitted data is saved by an object.
     *
     * @param    array $source    If provided, this array will override the
     *                            request array.
     *
     * @return    array
     */
    public function getRequestData(&$source = null)
    {
        if ($source === null) {
            $source = DRequest::load()
                              ->getParameters();
        }
        $data = array();
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            if ($field->hasValueInSource($source)) {
                $data[ $fieldName ] = $field->getValueFromSource(
                    $source,
                    $this->getFieldValue($fieldName)
                );
            }
        }

        return $data;
    }

    /**
     * Returns the name of the field that contains a string representation
     * of this model instance.
     *
     * @return    string
     */
    public static function getStringValueFieldName()
    {
        throw new DNotImplementedException(array(get_called_class(), __FUNCTION__));
    }

    /**
     * Checks if this model instance has unsaved changes.
     *
     * @return    bool
     */
    public function hasUnsavedChanges()
    {
        return ($this->id === 0
            || count($this->originalValues) > 0);
    }

    /**
     * Loads the data for the object if it is not a new object. This function must be called
     * by the objects constructor, after setting field information.
     *
     * @param    int $id          The id of the object to load. Passing an id of
     *                            0 will create a new object.
     *
     * @return    void
     * @throws    DUnknownModelInstanceException    If an invalid model instance ID is provided.
     */
    protected function load($id)
    {
        // Trigger onBeforeLoad event.
        $event = new DOnBeforeLoad();
        $this->triggerEvent($event);
        $this->loadFromDatabase((int)$id);
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
    abstract protected function loadFromDatabase($id, $reload = false);

    /**
     * Returns the definition for this model.
     *
     * @code
     * $definition = app\decibel\authorise\DUser::getDefinition();
     * @endcode
     *
     * @return    DDefinition
     */
    public static function getDefinition()
    {
        return DDefinition::load(get_called_class());
    }

    /**
     * Returns the qualified name of the definition for this model.
     *
     * @code
     * $definitionName = app\decibel\authorise\DUser::getDefinitionName();
     * @endcode
     *
     * @return    string
     */
    public static function getDefinitionName()
    {
        return get_called_class() . '_Definition';
    }

    /**
     * Saves the object to the database, subject to a variety of checks.
     *
     * The checks performed to ensure updating can take place are:
     * - The {@link app::decibel::model::DBaseModel::ON_BEFORE_SAVE DBaseModel::ON_BEFORE_SAVE} event will be
     * triggered.
     * - If this is a new object instance, the {@link app::decibel::model::DBaseModel::ON_BEFORE_FIRST_SAVE
     * DBaseModel::ON_BEFORE_FIRST_SAVE} event will be triggered.
     *
     * If any of these checks fail, an unsuccessful Result object will be returned, containing
     * the resons for its failure.
     *
     * If all checks are successful, the following actions will be performed:
     * - The {@link app::decibel::model::DBaseModel::ON_SAVE DBaseModel::ON_SAVE} event will be triggered.
     * - If this is a new object instance, the {@link app::decibel::model::DBaseModel::ON_FIRST_SAVE
     * DBaseModel::ON_FIRST_SAVE} event will be triggered.
     * - If this is not a new object instance, the {@link app::decibel::model::DBaseModel::ON_SUBSEQUENT_SAVE
     * DBaseModel::ON_SUBSEQUENT_SAVE} event will be triggered.
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
     */
    abstract public function save(DUser $user);

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
        // Merge default data with updated data for new objects.
        if ($this->id === 0) {
            foreach ($this->getFields() as $fieldName => $field) {
                /* @var $field DField */
                if (!isset($this->originalValues[ $fieldName ])) {
                    $this->originalValues[ $fieldName ] = isset($this->fieldValues[ $fieldName ])
                        ? $this->fieldValues[ $fieldName ]
                        : null;
                }
            }
        }
    }

    /**
     * Triggers internal and external event handling functions registered for
     * this object. Internal handlers are passed the event that occured as a
     * parameter.
     *
     * @warning
     * This method will throw a {@link DInvalidEventHandlerResultException}
     * if any of the bound handlers return an invalid result. Handlers must
     * return <code>null</code> or a {@link app::decibel::utility::DResult DResult}
     * object.
     *
     * @param    DModelEvent $event Event to be triggered.
     *
     * @return    DResult    Cummulative result of the bound event handlers.
     * @throws    DInvalidEventHandlerResultException    If a handler returns an invalid result.
     * @throws    DInvalidEventException    If the specified event is not produced by this dispatcher.
     */
    final public function triggerEvent(DModelEvent $event)
    {
        $result = new DResult();
        // Internal handlers.
        $result->merge(
            $this->triggerInternalEventHandlers(get_class($event))
        );
        // External handlers, including all parent and abstract parent handlers.
        $result->merge(
            $this->notifyObservers($event)
        );

        return $result;
    }

    /**
     * Triggers any internal events handlers that have been bound
     * to the specified event.
     *
     * @warning
     * This method will throw a {@link DInvalidEventHandlerResultException}
     * if any of the bound handlers return an invalid result. Handlers must
     * return <code>null</code> or a {@link app::decibel::utility::DResult DResult}
     * object.
     *
     * @param    string $event Name of the event to be handled.
     *
     * @return    DResult    Cummulative result of the bound event handlers.
     * @throws    DInvalidEventHandlerResultException    If a handler returns an invalid result.
     */
    protected function triggerInternalEventHandlers($event)
    {
        // If no handlers are bound, return null.
        $definition = $this->definition;
        $eventHandlers = $definition->getEventHandlers($event);
        $result = new DResult();
        foreach ($eventHandlers as $handler) {
            // Execute the event handler.
            $handlerResult = $this->$handler($event);
            // Check handler returned a valid result if debug mode is enabled.
            if ($handlerResult !== null
                && !$handlerResult instanceof DResult
            ) {
                $qualifiedName = get_class($this);
                throw new DInvalidEventHandlerResultException(
                    "{$qualifiedName}::{$handler}()",
                    "{$qualifiedName}-{$event}"
                );
            }
            // Append to results.
            $result->merge($handlerResult);
        }

        return $result;
    }

    /**
     * Determines if a user is authorised to delete this object.
     *
     * @param    DUser $user The user to test.
     *
     * @return    DResult
     * @throws    DUnprivilegedException    If the user does not have the required privilege.
     */
    public function userCanDelete(DUser $user)
    {
        // Check the delete privilege for this object, if it exists.
        $privilege = $this->getPrivilegeName(DPrivilege::SUFFIX_DELETE);
        $userPrivileges = DUserPrivileges::adapt($user);
        if (!$userPrivileges->hasPrivilege($privilege)) {
            throw new DUnprivilegedException($user, $privilege);
        }

        return new DResult(
            static::getDisplayName(),
            new DLabel('app\\decibel', 'deleted')
        );
    }

    /**
     * Determines if a user is authorised to save this object.
     *
     * @param    DUser $user The user to test.
     *
     * @return    DResult
     * @throws    DUnprivilegedException    If the user does not have the required privilege.
     */
    public function userCanSave(DUser $user)
    {
        // Determine correct privilege to check.
        if ($this->id === 0) {
            $privilege = $this->getPrivilegeName(DPrivilege::SUFFIX_CREATE);
        } else {
            $privilege = $this->getPrivilegeName(DPrivilege::SUFFIX_EDIT);
        }
        $userPrivileges = DUserPrivileges::adapt($user);
        if (!$userPrivileges->hasPrivilege($privilege)) {
            throw new DUnprivilegedException($user, $privilege);
        }

        return new DResult($this->displayName, 'saved');
    }

    /**
     * Validates the current field data for the object based on field
     * validation settings.
     *
     * @return    DResult
     */
    private function validate()
    {
        $result = new DResult($this->displayName, 'saved');
        // Check indexes.
        foreach ($this->definition->getIndexes() as $index) {
            /* @var $index DIndex */
            $result->merge($index->validate($this));
        }
        // Check updated fields.
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            if (array_key_exists($fieldName, $this->fieldValues)) {
                $result->merge($field->checkValue($this->fieldValues[ $fieldName ], $this));
            }
        }

        return $result;
    }

    /**
     * Returns a stdClass object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        $jsonObject = new stdClass();
        // Add fields for the model.
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            // Don't recurse into linked objects.
            if ($field instanceof DRelationalField) {
                $value = $this->fieldValues[ $fieldName ];
            } else {
                $value = $this->getFieldValue($fieldName);
            }
            $jsonObject->$fieldName = $value;
        }

        return $jsonObject;
    }

    /**
     * Returns a {@link app::decibel::model::search::DBaseModelSearch DBaseModelSearch}
     * that can be used to generate the list of available objects for linking
     * to by {@link app::decibel::model::field::DRelationalField DRelationalField}
     * fields.
     *
     * @param    array $options Additional options for the search.
     *
     * @return    DBaseModelSearch
     */
    public static function link($options = array())
    {
        return static::search()
                     ->sortByField('id')
                     ->includeFields(array(
                                         'id',
                                     ));
    }

    /**
     * Updates the object's data array with variables from the
     * request array where available. This should be overridden for
     * objects with non-standard fields to ensure continuity while
     * editing object data.
     *
     * @note
     * This method will ignore any provided parameters that cause a
     * {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * exception.
     *
     * @param    array $data      The data to merge. If not provided,
     *                            the data will be obtained using the
     *                            {@link app::decibel::model::DModel::getRequestData() DModel::getRequestData} method.
     *
     * @return    void
     * @todo    Read-only fields shouldn't be updated by this method, but this
     *            would break creation of child object (for example, creating
     *            a new taxonomy classification where DChild::$parent is not merged).
     */
    public function mergeRequestData(&$data = null)
    {
        $mergeData = $this->getRequestData($data);
        // Determine names of all fields for this model.
        // Only valid fields will be updated.
        $fields = array_keys($this->fields);
        // Find each of the object's fields in the request scope.
        foreach ($mergeData as $fieldName => $value) {
            if (in_array($fieldName, $fields)) {
                try {
                    $this->setFieldValue($fieldName, $value);
                } catch (DReadOnlyParameterException $e) {
                }
            }
        }
    }
}
