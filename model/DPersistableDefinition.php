<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\authorise\DUser;
use app\decibel\database\debug\DQueryExecutionException;
use app\decibel\database\DQuery;
use app\decibel\debug\DErrorHandler;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DDuplicateFieldNameException;
use app\decibel\model\debug\DUnsupportedFieldTypeException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DDateTimeField;
use app\decibel\model\field\DField;
use app\decibel\model\index\DIndex;
use app\decibel\model\search\DSearchable;
use app\decibel\regional\DLabel;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DDescribable;
use app\decibel\utility\DDescribableObject;
use app\decibel\utility\DPersistable;
use app\decibel\utility\DResult;

/**
 * Base class for simple definable classes that can be persisted.
 *
 * @author    Timothy de Paris
 */
abstract class DPersistableDefinition extends DDefinition
    implements DDefinable, DPersistable, DDescribable, DSearchable
{
    use DDefinableObject;
    use DDescribableObject;

    /**
     * 'Created' field name.
     *
     * @var        string
     */
    const FIELD_CREATED = 'created';

    /**
     * 'Enabled' option name.
     *
     * @var        string
     */
    const OPTION_ENABLED = 'enabled';

    /**
     * Constructs an instance of this definable object.
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DPersistableDefinition
     */
    protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $this->displayName = $qualifiedName::getDisplayName();
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
        // Add configurations to the definition.
        $enabled = new DBooleanField(self::OPTION_ENABLED, 'Available');
        $enabled->setRequired(true);
        $enabled->setDefault(true);
        $this->addConfiguration($enabled);
        $created = new DDateTimeField(self::FIELD_CREATED, new DLabel(self::class, self::FIELD_CREATED));
        $created->setReadOnly(true);
        $created->setExportable(false);
        $this->addField($created);
        $createdIndex = new DIndex('index_created');
        $createdIndex->addField($created);
        $this->addIndex($createdIndex);
    }

    /**
     * Adds a field to this object definition.
     *
     * @param    DField $field Definition of the field to add.
     *
     * @return    void
     * @throws    DDuplicateFieldNameException    If a field with this name
     *                                            has already been registered.
     * @throws    DUnsupportedFieldTypeException    If the field is not able to
     *                                            be added to this definition.
     */
    final public function addField(DField $field)
    {
        if (!$field->isNativeField()) {
            throw new DUnsupportedFieldTypeException($field, $this);
        }
        // Set information about the model that added the field.
        $field->setModelInformation($this->qualifiedName);
        // Pass field to parent to finish the process.
        parent::addField($field);
    }

    /**
     * Defines fields and indexes required by this object.
     *
     * @return    void
     */
    abstract protected function define();

    /**
     * Deletes the class instance from the database.
     *
     * @param    DUser $user The user attempting to delete the model instance.
     *
     * @return    DResult
     */
    abstract public function delete(DUser $user);

    /**
     * Executes a query and handles any errors.
     *
     * @param    DResult $result Result object to append any error messages to.
     * @param    string  $sql    SQL to execute.
     * @param    array   $values Optional values to pass to the query.
     *
     * @return    DResult
     */
    protected static function executeQuery(DResult $result, $sql, array $values = array())
    {
        try {
            new DQuery($sql, $values);
        } catch (DQueryExecutionException $exception) {
            DErrorHandler::throwException($exception);
            $result->setSuccess(false, $exception->getMessage());
        }

        return $result;
    }

    /**
     * Returns the definition for this model.
     *
     * @code
     * $definition = app\decibel\authorise\auditing\DAuthenticationRecord::getDefinition();
     * @endcode
     *
     * @return    DDefinition
     */
    public static function getDefinition()
    {
        return DDefinition::load(get_called_class());
    }

    /**
     * Returns the qualified name of the definition for this object.
     *
     * @code
     * $definitionName = app\decibel\authorise\auditing\DAuthenticationRecord::getDefinitionName();
     * @endcode
     *
     * @return    string
     */
    public static function getDefinitionName()
    {
        return get_called_class();
    }

    /**
     * Builds the SQL query required to save a record of this type.
     *
     * @return    string
     */
    abstract protected function getSaveSql();

    /**
     * Returns a list of values for this index record that have been serialised
     * for database storage.
     *
     * @return    array
     */
    protected function getSerialisedValues()
    {
        $serialisedValues = array();
        foreach ($this->getFields() as $fieldName => $field) {
            /* @var $field DField */
            if (isset($this->fieldValues[ $fieldName ])) {
                $serialisedValues[ $fieldName ] = $this->fieldValues[ $fieldName ];
            } else {
                $serialisedValues[ $fieldName ] = $field->serialize(
                    $field->getDefaultValue()
                );
            }
        }

        return $serialisedValues;
    }

    /**
     * Saves data from the class instance to the database.
     *
     * @param    DUser $user The user attempting to save the model instance.
     *
     * @return    DResult
     */
    final public function save(DUser $user)
    {
        $displayName = static::getDisplayName();
        $result = new DResult($displayName, new DLabel('app\\decibel', 'saved'));
        // Check that auditing is enabled.
        if (!$this->getOption(self::OPTION_ENABLED)) {
            $result->setSuccess(false, $displayName . ' is currently disabled');
        } else {
            // Set any required field values.
            $this->setDefaultValues();
            // Data checks
            $result->merge($this->validate());
            if ($result->isSuccessful()) {
                $result = static::executeQuery(
                    $result,
                    $this->getSaveSql(),
                    $this->getSerialisedValues()
                );
            }
        }

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
    abstract protected function setDefaultValues();

    /**
     * Validates data for this object instance.
     *
     * @return    DResult
     */
    protected function validate()
    {
        $result = new DResult();
        foreach ($this->fields as $fieldName => $field) {
            /* @var $field DField */
            if (isset($this->fieldValues[ $fieldName ])) {
                $value = $this->fieldValues[ $fieldName ];
            } else {
                $value = null;
            }
            $result->merge($field->checkValue($value, $this));
        }

        return $result;
    }
}
