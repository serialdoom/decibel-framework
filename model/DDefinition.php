<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\application\DConfigurationManager;
use app\decibel\configuration\debug\DUnknownConfigurationOptionException;
use app\decibel\database\schema\DIndexDefinition;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidMethodCallException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\model\debug\DDuplicateConfigurationOptionException;
use app\decibel\model\debug\DDuplicateFieldNameException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DIdField;
use app\decibel\model\field\DNumericField;
use app\decibel\model\field\DReservedFieldNameException;
use app\decibel\model\index\DIndex;
use app\decibel\model\index\DUniqueIndex;
use app\decibel\registry\DInvalidClassNameException;
use app\decibel\utility\DBaseClass;

/**
 * Defines the base class for model definitions.
 *
 * @author    Timothy de Paris
 */
abstract class DDefinition
{
    use DBaseClass;
    use DDefinitionCache;

    /**
     * List of fields names that are able to be added despite being
     * properties of a model.
     *
     * @var        array
     */
    private static $nonReservedFields = array(
        'id',
        'guid',
        'qualifiedName',
    );

    /**
     * List of fields names that are not able to be added despite not being
     * properties of a model.
     *
     * @var        array
     */
    private static $reservedFields = array(
        'hasChanged',
        'indexes',
        'options',
        'eventHandlers',
        'inheritanceHierarchy',
        'fields',
    );

    /**
     * Array containing database indexes for this model.
     *
     * @var        array
     */
    protected $indexes = array();

    /**
     * Available configuration options for this model.
     *
     * @var        array
     */
    protected $configurations = array();

    /**
     * Options for this model.
     *
     * @var        array
     */
    protected $options = array();

    /**
     * Qualfied name of the model.
     *
     * @var        string
     */
    protected $qualifiedName;

    /**
     * Display name of the model.
     *
     * @var        string
     */
    protected $displayName;

    /**
     * Name of the table that stores data for this model.
     *
     * @var        string
     */
    protected $tableName;

    /**
     * Whether this definition is for an abstract class.
     *
     * @var        bool
     */
    protected $isAbstract = false;

    /**
     * Constructs an instance of this class
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    static
     */
    protected function __construct($qualifiedName)
    {
        $this->qualifiedName = $qualifiedName;
        $this->fields = array();
    }

    /**
     * Returns object parameters.
     *
     * @param    string $name The name of the parameter to retrieve.
     *
     * @return    mixed
     */
    public function __get($name)
    {
        // Default for valid properties.
        if (property_exists($this, $name)) {
            $this->notifyDeprecatedPropertyAccess($name);

            return $this->$name;
        }
        throw new DInvalidPropertyException($name);
    }

    /**
     * Returns an array of cacheable fields.
     *
     * @return    void
     */
    public function __sleep()
    {
        return array(
            'qualifiedName',
            'displayName',
            'tableName',
            'configurations',
            'fields',
            'indexes',
            'options',
        );
    }

    /**
     * Loads a definition.
     *
     * @param    string $definable    Qualified name of the definable class
     *                                to load a definition for.
     *
     * @return    DDefinition
     * @throws    DInvalidClassNameException    If the definition class does not exist.
     */
    public static function load($definable)
    {
        return self::retrieve($definable);
    }

    /**
     * Adds a confiugration to this model.
     *
     * @param    DField $configuration Definition of the configuration to add.
     *
     * @return    void
     * @throws    DDuplicateConfigurationOptionException    If a configuration option with
     *                                                    this name has already been registered.
     */
    public function addConfiguration(DField $configuration)
    {
        // Check for duplicate configuration option names.
        $configurationName = $configuration->getName();
        if (isset($this->configurations[ $configurationName ])
            || isset($this->options[ $configurationName ])
        ) {
            throw new DDuplicateConfigurationOptionException(
                $configurationName,
                $this->qualifiedName
            );
        }
        $this->configurations[ $configurationName ] = $configuration;
    }

    /**
     * Adds a field to this model.
     *
     * @param    DField $field Definition of the field to add.
     *
     * @return    void
     * @throws    DReservedFieldNameException        If the field's name will conflict
     *                                            with a property of the model
     *                                            associated with this definition.
     * @throws    DDuplicateFieldNameException    If a field with this name
     *                                            has already been registered.
     * @throws    DInvalidMethodCallException        If this method is called before
     *                                            {@link DModel_Definition::__construct()}.
     */
    public function addField(DField $field)
    {
        // Make sure parent constructor has been called first as this function
        // can be called in an overridden constructor.
        if ($this->qualifiedName === null) {
            $calledClass = get_called_class();
            throw new DInvalidMethodCallException(
                array($calledClass, __FUNCTION__),
                "<code>{$calledClass}::__construct()</code> must be called first."
            );
        }
        // Check for duplicate or reserved field names.
        $fieldName = $field->getName();
        $this->checkFieldName($fieldName);
        $this->fields[ $fieldName ] = $field;
    }

    /**
     * Adds an index to the model.
     *
     * @param    DIndex $index The index description.
     *
     * @return    void
     */
    public function addIndex(DIndex $index)
    {
        $this->indexes[ $index->getName() ] = $index;
    }

    /**
     * Checks the provided field name for validity for this definition.
     *
     * @note
     * This method will return <code>true</code> if the field name is valid,
     * however will throw an exception if the field name is not valid.
     *
     * @param    string $fieldName Field name to check.
     *
     * @return    bool
     * @throws    DReservedFieldNameException        If the field's name will conflict
     *                                            with a property of the model
     *                                            associated with this definition.
     * @throws    DDuplicateFieldNameException    If a field with this name
     *                                            has already been registered.
     */
    public function checkFieldName($fieldName)
    {
        // Check if a field already exists with this name.
        if (isset($this->fields[ $fieldName ])) {
            throw new DDuplicateFieldNameException(
                $fieldName,
                $this->qualifiedName,
                $this->fields[ $fieldName ]->addedBy
            );
        }
        if (!in_array($fieldName, self::$nonReservedFields)
            && (property_exists($this->qualifiedName, $fieldName)
                || in_array($fieldName, self::$reservedFields))
        ) {
            throw new DReservedFieldNameException($fieldName);
        }

        return true;
    }

    /**
     * Returns the definition of the configuration with the specified name.
     *
     * @param    string $name
     *
     * @return    DField
     */
    public function getConfiguration($name)
    {
        if (isset($this->configurations[ $name ])) {
            $configuration = $this->configurations[ $name ];
        } else {
            $configuration = null;
        }

        return $configuration;
    }

    /**
     * Returns available configurations for this definition.
     *
     * @return    array
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Returns the field with the specified name.
     *
     * @param    string $name Name of the field.
     *
     * @return    DField
     * @throws    DInvalidPropertyException    If no field exists with the specified name.
     */
    public function getField($name)
    {
        if (!isset($this->fields[ $name ])) {
            $exception = new DInvalidPropertyException($name, get_class($this));
            DErrorHandler::throwException($exception);
        }

        return $this->fields[ $name ];
    }

    /**
     * Returns any indexes for this definition.
     *
     * @return    array    List of {@link DIndex} objects.
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Retrieves the value of an option for this model.
     *
     * This function handles static options set by {@link DDefinition::setOption()}
     * as well as user defined configurations.
     *
     * @param    string $option The option to retrieve.
     *
     * @return    mixed    The value of the option, or null if
     *                    the option has not been set.
     */
    final public function getOption($option)
    {
        if (isset($this->options[ $option ])) {
            $value = $this->options[ $option ];
        } else {
            if (isset($this->configurations[ $option ])) {
                $configurationManager = DConfigurationManager::load();
                $value = $configurationManager->getClassConfiguration(
                    $this->qualifiedName,
                    $option
                );
                // Retrieve the default value if this option hasn't been set.
                if ($value === null) {
                    $value = $this->configurations[ $option ]->getDefaultValue();
                }
            } else {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * Returns a list of {@link app::decibel::model::field::DFieldSearch DFieldSearch}
     * describing how a search can be performed on this object.
     *
     * @param    array $values Current search values.
     * @param    bool  $sort   Whether to sort the returned indexes.
     *
     * @return    array    List of {@link app::decibel::model::field::DFieldSearch DFieldSearch}
     *                    objects.
     */
    public function getSearchableFields(array $values = array(), $sort = true)
    {
        $searchableFields = array();
        // Add searchable fields
        foreach ($this->fields as $fieldName => $field) {
            /* @var $field DField */
            $options = $field->getSearchOptions();
            if ($options === null) {
                continue;
            }
            if (isset($values[ $fieldName ]['value'])) {
                $options->setValue($values[ $fieldName ]['value']);
            }
            if (isset($values[ $fieldName ]['operator'])) {
                $options->setOperator($values[ $fieldName ]['operator']);
            }
            $searchableFields[] = $options;
        }
        // Sort the indexes if requested.
        if ($sort) {
            usort($searchableFields, array(DFieldSearch::class, 'sort'));
        }

        return $searchableFields;
    }

    /**
     * Returns a list of {@link app::decibel::model::field::DFieldSearch DFieldSearch}
     * describing how a search can be performed on this object.
     *
     * @param    array $values Current search values.
     *
     * @return    array    List of {@link app::decibel::model::field::DFieldSearch DFieldSearch}
     *                    objects.
     */
    public function getSearchOptions(array $values = array())
    {
        $searchOptions = $this->getSearchableFields($values, false);
        usort($searchOptions, array(DFieldSearch::class, 'sort'));

        return $searchOptions;
    }

    /**
     * Tests a field to determine if it's values will be unique across
     * all model instances.
     *
     * Unique fields must either have a {@link app::decibel::model::index::DUniqueIndex DUniqueIndex}
     * defined or be a {@link app::decibel::model::field::DNumericField DNumericField}
     * with the <code>autoincrement</code> parameter set to <code>true</code>.
     *
     * @param    string $fieldName Name of the field to test.
     *
     * @return    bool
     */
    public function isFieldUnique($fieldName)
    {
        /* @var $field DField */
        $field = $this->fields[ $fieldName ];
        if ($field instanceof DIdField
            || ($field instanceof DNumericField
                && $field->autoincrement)
        ) {
            $unique = true;
        } else {
            $unique = false;
            // Check unique indexes.
            foreach ($this->indexes as $index) {
                /* @var $index DIndex */
                if ($index instanceof DUniqueIndex
                    && in_array($fieldName, array_keys($index->getFields()))
                ) {
                    $unique = true;
                    break;
                }
            }
        }

        return $unique;
    }

    /**
     * Sets the default value for an configuration option.
     *
     * @param    string $option       Name of the option.
     * @param    mixed  $defaultValue The default value.
     *
     * @return    void
     * @throws    DUnknownConfigurationOptionException    If the specified option
     *                                                    is not valid for this
     *                                                    definition.
     */
    protected function setDefaultConfigurationValue($option, $defaultValue)
    {
        if (!isset($this->configurations[ $option ])) {
            throw new DUnknownConfigurationOptionException($option);
        }
        /* @var $configuration DField */
        $configuration = $this->configurations[ $option ];
        $configuration->setDefault($defaultValue);
    }

    /**
     * Sets an option for this model.
     *
     * @param    string $option The option to set.
     * @param    mixed  $value  The option value.
     *
     * @return    void
     */
    public function setOption($option, $value)
    {
        $this->options[ $option ] = $value;
    }

    /**
     * Creates a new {@link app::decibel::database::DTableDefinition DTableDefinition}
     * from this definition.
     *
     * @return    DTableDefinition
     */
    public function getTableDefinition()
    {
        $tableDefinition = new DTableDefinition($this->tableName);
        $fieldNames = array();
        foreach ($this->fields as $field) {
            /* @var $field DField */
            if (!$field->isSharedTable()) {
                $columnDefinition = $field->getDefinition();
                if ($columnDefinition) {
                    $tableDefinition->addColumn($columnDefinition);
                    $fieldNames[] = $columnDefinition->getName();
                }
            }
        }
        foreach ($this->indexes as $index) {
            /* @var $index DIndex */
            $indexDefinition = DIndexDefinition::createFromDIndex($index);
            if ($indexDefinition) {
                // Check if this index is for this table's fields.
                $indexColumns = $indexDefinition->getColumns();
                $intersection = array_intersect($fieldNames, $indexColumns);
                if (count($intersection) === count($indexColumns)) {
                    $tableDefinition->addIndex($indexDefinition);
                }
            }
        }

        return $tableDefinition;
    }
}
