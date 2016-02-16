<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\configuration\DApplicationMode;
use app\decibel\database\schema\DColumnDefinition;
use app\decibel\database\statement\DJoin;
use app\decibel\database\statement\DLeftJoin;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\DLightModel;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DInvalidFieldNameException;
use app\decibel\model\field\DReservedFieldNameException;
use app\decibel\registry\DClassQuery;
use app\decibel\utility\DBasicDefinable;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DResult;
use app\decibel\validator\DIdentifierValidator;
use app\decibel\validator\DValidator;
use app\DecibelCMS\Model\Field\DFieldWidgetMapper;

/**
 * %DField is the base class for all types of fields that can be added to models
 * within the framwork.
 *
 * This class abstracts each property of a model from the database column that
 * stores that property's data. Fields are defined within each model's
 * definition file.
 *
 * A range of standard field types are provided to suit most requirements. Each
 * of these types provide various options to configure the parameters of the
 * data that will be stored.
 *
 * If neccessary, additional custom field types can be implemented by extending
 * %DField or one of its descendant classes.
 *
 * While the {@link DField} class allows a field to be defined, a range of decorators
 * are implemented to provide field based functionality:
 * - {@link DFieldDatabaseMapper} to describe how data for a field can be retrieved
 *        from and stored in the database.
 * - {@link DFieldValidator} to describe how to validate field data.
 *
 * @author         Timothy de Paris
 * @see            @ref model_fields
 */
abstract class DField implements DAdaptable, DDecoratable, DExportable, DRandomisable
{
    use DBasicDefinable;
    use DAdapterCache;
    use DDecoratorCache;
    use DExportableField;
    use DRandomisableField;

    /**
     * 'TINYINT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_TINYINT = 'tinyint';

    /**
     * 'SMALLINT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_SMALLINT = 'smallint';

    /**
     * 'MEDIUMINT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_MEDIUMINT = 'mediumint';

    /**
     * 'INT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_INT = 'int';

    /**
     * 'BIGINT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_BIGINT = 'bigint';

    /**
     * 'FLOAT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_FLOAT = 'float';

    /**
     * 'DATE' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_DATE = 'date';

    /**
     * 'VARCHAR' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_VARCHAR = 'varchar';

    /**
     * 'TEXT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_TEXT = 'text';

    /**
     * 'MEDIUMTEXT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_MEDIUMTEXT = 'mediumtext';

    /**
     * 'MEDIUMTEXT' MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_LONGTEXT = 'longtext';

    /**
     * Special MySQL data type.
     *
     * @var        string
     */
    const DATA_TYPE_SPECIAL = 'special';

    ///@cond INTERNAL
    /**
     * 'Type' validation rule key.
     *
     * @var        string
     */
    const VALIDATION_RULE_TYPE = 'type';

    ///@endcond
    ///@cond INTERNAL
    /**
     * 'Message' validation rule key.
     *
     * @var        string
     */
    const VALIDATION_RULE_MESSAGE = 'message';

    ///@endcond
    ///@cond INTERNAL
    /**
     * Name of the label representing missing value.
     *
     * @var        string
     */
    const LABEL_VALUE_MUST_PROVIDED = 'valueMustBeProvided';

    ///@endcond
    ///@cond INTERNAL
    /**
     * Name of the label representing missing value in the language.
     *
     * @var        string
     *
     */
    const LABEL_VALUE_MUST_PROVIDED_IN_LANGUAGE = 'valueMustBeProvidedInLanguage';

    ///@endcond
    /**
     * Contains the data types able to be stored in native database formats
     * within model tables.
     *
     * @var        array
     */
    protected static $nativeDataTypes = array(
        self::DATA_TYPE_TINYINT,
        self::DATA_TYPE_SMALLINT,
        self::DATA_TYPE_MEDIUMINT,
        self::DATA_TYPE_INT,
        self::DATA_TYPE_BIGINT,
        self::DATA_TYPE_FLOAT,
        self::DATA_TYPE_DATE,
        self::DATA_TYPE_VARCHAR,
        self::DATA_TYPE_TEXT,
        self::DATA_TYPE_MEDIUMTEXT,
    );

    /**
     * Reserved field names.
     *
     * Using one of these names will trigger a {@link DReservedFieldNameException}
     *
     * @var        array
     */
    protected static $reservedFieldNames = array(
        'post',
        'get',
        'definition',
        'fields',
    );

    /**
     * List of data types for which values should not be quoted in queries.
     *
     * @var        array
     */
    protected static $unquotedDataTypes = array(
        self::DATA_TYPE_TINYINT,
        self::DATA_TYPE_SMALLINT,
        self::DATA_TYPE_MEDIUMINT,
        self::DATA_TYPE_INT,
        self::DATA_TYPE_BIGINT,
        self::DATA_TYPE_FLOAT,
    );

    /**
     * The database name of the field.
     *
     * @var        string
     */
    protected $name;

    /**
     * The database mapper for this field.
     *
     * @note
     * The mapper is cached here after first accessed via {@link DField::getDatabaseMapper()}.
     *
     * @var        DFieldDatabaseMapper
     */
    private $databaseMapper;

    /**
     * Qualified name of the model that owns this field.
     *
     * @var        string
     */
    protected $owner;

    /**
     * Name of the table that stores data for the model that owns this field.
     *
     * @var        string
     */
    protected $ownerTable;

    /**
     * Qualified name of the model that added this field.
     *
     * @var        string
     */
    protected $addedBy;

    /**
     * Name of the table that stores data for the model that added this field.
     *
     * @var        string
     */
    protected $addedByTable;

    /**
     * The human readable name of this field.
     *
     * @var        string
     */
    protected $displayName;

    /**
     * The default value for this field.
     *
     * @var        mixed
     */
    protected $defaultValue;

    /**
     * A description of the field.
     *
     * @var        string
     */
    protected $description;

    /**
     * Validation rules assigned to this field.
     *
     * @var        array
     */
    private $validationRules = array();

    /**
     * Option specifying that the value of this field cannot be modified
     * by the user.
     *
     * @var        bool
     */
    protected $readOnly = false;

    /**
     * Whether this field is required.
     *
     * If set to true, a value must be provided before the object can be saved.
     *
     * @var        bool
     */
    protected $required = false;

    /**
     * Allows the user to select a null value (i.e. not any specific option).
     *
     * The value of this option should be the string that will be used to
     * describe a null value to the user (e.g. 'None Specified').
     *
     * @var        string
     */
    protected $nullOption;

    /**
     * Creates a new field definition.
     *
     * @param    string $name             The database name of this field.
     *                                    Must be written in camelCase and be no longer than 40 characters.
     * @param    mixed  $displayName      The human-readable name of this field.
     *                                    This can be a string or a DLable object
     *                                    for multi-lingual models.
     *
     * @return    static
     * @throws    DInvalidFieldNameException    If the name for the field is invalid.
     * @throws    DReservedFieldNameException    If the name for the field is reserved
     *                                        for internal purposes.
     */
    final public function __construct($name, $displayName)
    {
        // Test provided field name for validity.
        if (!DApplicationMode::isProductionMode()) {
            $this->validateFieldName($name);
        }
        // Store field information.
        $this->name = $name;
        $this->displayName = $displayName;
        // Set default options for the field.
        $this->setDefaultOptions();
    }

    /**
     * Attempts to convert the provided data into a value that
     * can be assigned to a field of this type.
     *
     * @warning
     * This method will throw a {@link DInvalidFieldValueException}
     * if the provided value cannot be cast. This may be due to the data type
     * being incompatible, or the provided value not meeting specific criteria
     * of this field (for example, character length of a string).
     * In Production Mode, the exception will be automatically handled
     * and execution will continue.
     *
     * @param    mixed $value The value to cast.
     *
     * @return    mixed    The cast value
     * @throws    DInvalidFieldValueException    If the provided value cannot
     *                                        be cast for this field.
     */
    abstract public function castValue($value);

    /**
     * Checks the supplied data against each of the registered
     * validation rules for this field.
     *
     * @param    mixed      $data      The data requiring validation.
     * @param    DDefinable $definable The object that requested validation.
     *
     * @return    DResult
     * @deprecated    In favour of {@link DFieldValidator}
     */
    public function checkValue($data, DDefinable $definable = null)
    {
        $validator = DFieldValidator::decorate($this);

        return $validator->validate($data, $definable);
    }

    /**
     * Compares to values for this field to determine if they are equal.
     *
     * @param    mixed $value1 The first value.
     * @param    mixed $value2 The second value.
     *
     * @return    bool    true if the values are equal, false otherwise.
     */
    public function compareValues($value1, $value2)
    {
        try {
            $equal = ($this->serialize($value1) === $this->serialize($value2));
        } catch (DInvalidFieldValueException $exception) {
            $equal = false;
        }

        return $equal;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * This is used by the {@link DField::castValue()} method
     * when performing a strict validation of field data.
     *
     * @return    string    Qualified class name or a PHP data type as returned
     *                    by the PHP <code>gettype()</code> function.
     *                    See http://php.net/gettype for more details.
     */
    abstract public function getInternalDataType();

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    abstract public function getDataType();

    /**
     * Returns the database mapper for this field.
     *
     * @return    DFieldDatabaseMapper
     */
    final public function getDatabaseMapper()
    {
        if ($this->databaseMapper === null) {
            $this->databaseMapper = DFieldDatabaseMapper::adapt($this);
        }

        return $this->databaseMapper;
    }

    /**
     * Returns the default value for this field instance.
     *
     * @return    mixed
     */
    public function getDefaultValue()
    {
        if ($this->defaultValue === null) {
            $this->defaultValue = $this->getStandardDefaultValue();
        }

        return $this->defaultValue;
    }

    /**
     * Returns a human-readable description of the internal data type
     * requirements of this field.
     *
     * This description is used by the {@link DInvalidFieldValueException}
     * class when thrown by the {@link DField::castValue()} method.
     *
     * @return    string
     */
    public function getInternalDataTypeDescription()
    {
        return $this->getInternalDataType();
    }

    /**
     * Returns the name of this field.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the null option for this field.
     *
     * @return    mixed
     */
    public function getNullOption()
    {
        return $this->nullOption;
    }

    /**
     * Returns the qualified name of the model to which this field belongs.
     *
     * @return    string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Returns a regular expression that can be used to match the string
     * version of data for this field.
     *
     * @return    string    A regular expression, or <code>null</code> if it is
     *                    not possible to match via a regular expression.
     */
    public function getRegex()
    {
        return null;
    }

    /**
     * Returns information about how the fields used by this index can be searched.
     *
     * @return    DFieldSearch    The object describing how search can be
     *                            performed, or null if search is not allowed
     *                            or possible.
     */
    public function getSearchOptions()
    {
        return null;
    }

    /**
     * Returns the default value for this type of field.
     *
     * @note
     * This value will be used if no default value is supplied for a field instance.
     *
     * @return    string
     */
    abstract public function getStandardDefaultValue();

    /**
     * Returns the validation rules for this field.
     *
     * @return    array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }

    /**
     * Returns any data available for this field from the provided source.
     *
     * @param    ArrayAccess $source       The source.
     * @param    mixed       $currentValue The current value, for comparison.
     *
     * @return    mixed    The field value.
     */
    public function getValueFromSource($source, $currentValue = null)
    {
        // Prepare the widget and check.
        $widgetMapper = DFieldWidgetMapper::decorate($this);
        $value = $widgetMapper->getWidget()->getValueFromSource($source, $currentValue);

        return $this->castValue($value);
    }

    /**
     * Determines if any data is available for this field from the provided source.
     *
     * @param    ArrayAccess $source The source.
     *
     * @return    bool
     */
    public function hasValueInSource($source)
    {
        // Prepare the widget and check.
        $widgetMapper = DFieldWidgetMapper::decorate($this);

        return $widgetMapper->getWidget()->hasValueInSource($source);
    }

    /**
     * Determines if the provided value is considered empty for this field.
     *
     * @param    mixed $value The value to test.
     *
     * @return    bool
     */
    public function isEmpty($value)
    {
        return $value !== 0
        && (empty($value)
            || $this->isNull($value));
    }

    /**
     * Determines if this field is inherited from an ancestor definition.
     *
     * @return    bool
     */
    public function isInherited()
    {
        return ($this->owner !== $this->addedBy);
    }

    /**
     * Determines if this field is a native data type (i.e. is stored in the
     * main table for this model, as opposed to a shared table).
     *
     * @return    bool
     */
    public function isNativeField()
    {
        return in_array($this->getDataType(), self::$nativeDataTypes);
    }

    /**
     * Determines if the provided value is considered to be equal
     * to <code>null</code> for this field.
     *
     * @param    mixed $value The value to test.
     *
     * @return    bool
     */
    public function isNull($value)
    {
        return ($value === null);
    }

    /**
     * Determines if this field can be used for ordering.
     *
     * @return    bool
     */
    public function isOrderable()
    {
        return true;
    }

    /**
     * Determines if a value is required for this field.
     *
     * @return    bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    abstract protected function setDefaultOptions();

    /**
     * Prepares field data for saving to the database.
     *
     * @param    mixed $data The data to serialize.
     *
     * @return    mixed    The serialized data.
     */
    public function serialize($data)
    {
        return $this->getDatabaseMapper()
                    ->serialize($data);
    }

    /**
     * Converts a data value for this field to its string equivalent.
     *
     * @param    mixed $data The data to convert.
     *
     * @return    string    The string value of the data.
     */
    abstract public function toString($data);

    ///@cond INTERNAL
    /**
     * Provides debugging information about a value for this field.
     *
     * Used by the {@link app::decibel::model::DModel::generateDebug() DModel::generateDebug()} method.
     *
     * @param    mixed $data          Data to convert.
     * @param    bool  $showType      Pointer in which a decision about whether
     *                                the datatype of the debug message should
     *                                be shown will be returned.
     *
     * @return    string    The debugged data as a string.
     */
    public function debugValue($data, &$showType)
    {
        if ($this->nullOption !== null
            && $this->isNull($data)
        ) {
            $showType = false;
            $message = "NULL [{$this->nullOption}]";
        } else {
            $showType = true;
            $message = $this->toString($data);
        }

        return $message;
    }
    ///@endcond
    /**
     * Adds a validation rule to this field.
     *
     * @param    DValidator $type         The type of validation to be performed.
     * @param    string     $message      The message to be returned on validation failure.
     *                                    If not provided, any messages returned from
     *                                    the validation function will be displayed.
     *                                    The variable @#fieldName@# can be included in the message
     *                                    and will be replaced with the human-readable name
     *                                    of the field if returned by the validation function.
     *
     * @return    void
     */
    final public function addValidationRule(DValidator $type, $message = false)
    {
        $this->validationRules[] = array(
            self::VALIDATION_RULE_TYPE    => $type,
            self::VALIDATION_RULE_MESSAGE => $message,
        );
    }

    /**
     * Determines if this field has been assigned a validation rule
     * of the specified type.
     *
     * @param    string $type Qualified name of the validator to check for.
     *
     * @return    boolean
     */
    final public function hasValidationRule($type)
    {
        $hasRule = false;
        foreach ($this->validationRules as $validationRule) {
            if ($validationRule[ self::VALIDATION_RULE_TYPE ] instanceof $type) {
                $hasRule = true;
                break;
            }
        }

        return $hasRule;
    }

    /**
     * Returns a human readable description of the field.
     *
     * @return    mixed    A {@link app::decibel::regional::DLabel DLabel}
     *                    or string description, or <code>null</code>
     *                    if no description is available.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the human-readable name of this field.
     *
     * @return    string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    ///@cond INTERNAL
    /**
     * Process the value for this field within a row of model search results.
     *
     * @param    array  $row   The results to process.
     * @param    string $alias Alias of the field to process.
     *
     * @return    void
     */
    public function processRow(array &$row, $alias = null)
    {
        if ($alias === null) {
            $alias = $this->name;
        }
    }
    ///@endcond
    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $tableSuffix A suffix to append to the table name.
     *
     * @return    string
     */
    public function getFieldSql($tableSuffix = '')
    {
        return $this->getSelectSql(null, $tableSuffix);
    }

    /**
     * Returns SQL allowing selection of this field from the database,
     * returning the string value of the field where possible.
     *
     * @param    string $alias            If provided, this alias will be applied
     *                                    to the field in the returned SQL.
     * @param    string $tableSuffix      A suffix to append to the table name.
     * @param    DJoin  $joinFrom         A {@link app::decibel::database::statement::DJoin DJoin}
     *                                    object representing the    left side of this
     *                                    join. If not provided, the lowest level
     *                                    of the model hierarchy will be joined from.
     *
     * @return    array
     */
    public function getStringValueSql($alias = null, $tableSuffix = '',
                                      DJoin $joinFrom = null)
    {
        return array(
            'join' => null,
            'sql'  => $this->getSelectSql($alias, $tableSuffix),
        );
    }

    ///@cond INTERNAL
    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $alias            If provided, this alias will be applied
     *                                    to the field in the returned SQL.
     * @param    string $tableSuffix      A suffix to append to the table name.
     *
     * @return    string
     * @deprecated    In favour of {@link DFieldDatabaseMapper::getSelectSql()}
     */
    public function getSelectSql($alias = null, $tableSuffix = '')
    {
        return $this->getDatabaseMapper()
                    ->getSelectSql($alias, $tableSuffix);
    }
    ///@endcond
    /**
     * Returns sql representing this field whch can be placed in ORDER BY clause.
     *
     * @return    array
     * @todo          Remove this method.
     * @deprecated    In favour of {@link DField::getSortSql()}
     */
    public function getSortOptions()
    {
        return array(
            'join' => array($this->getJoin('', null)),
            'sql'  => $this->getFieldSql(),
        );
    }

    /**
     * Returns information about how this field can be joined to from the
     * table of the field's object.
     *
     * @param    string $tableSuffix      A suffix to append to the table name.
     * @param    DJoin  $joinFrom         A {@link app::decibel::database::statement::DJoin DJoin}
     *                                    object representing the    left side of this
     *                                    join. If not provided, the lowest level
     *                                    of the model hierarchy will be joined from.
     *
     * @return    DJoin
     */
    public function getJoin($tableSuffix = '', DJoin $joinFrom = null)
    {
        // Determine which table to join from.
        $leftSide = $this->getJoinTable($joinFrom);
        $rightSide = $this->getTable();
        // No need to join.
        if ($rightSide === $leftSide) {
            $join = null;
        } else {
            $on = "`{$leftSide}`.`id`=`{$rightSide}{$tableSuffix}`.`id`";
            $join = new DJoin($rightSide, $on, $rightSide . $tableSuffix);
        }

        return $join;
    }

    /**
     * Creates a join between the provided fields.
     *
     * @param    DField $to               The field to join to.
     * @param    string $fromAlias        The alias used for the join
     *                                    to the from field.
     * @param    string $aliasSuffix      The current alias suffix for this part
     *                                    of the search.
     *
     * @return    DJoin
     */
    public function getJoinTo(DField $to, $fromAlias, $aliasSuffix)
    {
        $toModel = $to->owner;
        if (DClassQuery::isValidClassName($toModel, DLightModel::class)) {
            $toTable = DDatabaseMapper::getTableNameFor($toModel);
            $toAlias = $toTable . $aliasSuffix;

            return new DLeftJoin(
                $toTable,
                "(`{$fromAlias}`.`{$this->getName()}`=`{$toAlias}`.`id`)",
                $toAlias
            );
        } else {
            $toTable = DDatabaseMapper::getTableNameFor(DModel::class);
            $toAlias = $toTable . $aliasSuffix;

            return new DLeftJoin(
                $toTable,
                "(`{$fromAlias}`.`{$this->getName()}`=`{$toAlias}`.`id`)",
                $toAlias
            );
        }
    }

    /**
     * Returns the name of the table to be joined from based on the provided
     * qualified name from the hierarchy of the field's owner.
     *
     * @param    DJoin $joinFrom          A {@link app::decibel::database::statement::DJoin DJoin}
     *                                    object representing the left side of this
     *                                    join. If not provided, the lowest level
     *                                    of the model hierarchy will be joined from.
     *
     * @return    string
     */
    protected function getJoinTable(DJoin $joinFrom = null)
    {
        // Determine which table to join from based on the
        // provided join level.
        if ($joinFrom === null) {
            $table = $this->ownerTable;
        } else {
            $table = $joinFrom->getAlias();
        }

        return $table;
    }

    /**
     * Returns the name of the table that stores information for this field
     * in the datbase.
     *
     * @return    string
     */
    public function getTable()
    {
        return $this->addedByTable;
    }

    /**
     * Returns the sql required to update data in this field.
     *
     * @return    string
     */
    final public function getUpdateSql()
    {
        // For MySQL Strict mode support.
        if (in_array($this->getDataType(), self::$unquotedDataTypes)) {
            $sql = "`{$this->name}`=#{$this->name}#";
        } else {
            $sql = "`{$this->name}`='#{$this->name}#'";
        }

        return $sql;
    }

    /**
     * Returns information about utilisation of other model instances
     * by this field for the provided model instance.
     *
     * @param    DModel $instance The model instance being indexed.
     *
     * @return    array    List of utilisation, with model instance IDs as keys
     *                    and relational integrity types as values.
     */
    public function getUtilisation(DModel $instance)
    {
        return array();
    }

    /**
     * Determines if this field has a description.
     *
     * @return    bool    true if this field has a description,
     *                    otherwise false
     */
    public function hasDescription()
    {
        return ($this->description !== null);
    }

    /**
     * Determines whether data for this field is stored in a shared object table.
     *
     * @return    bool
     */
    public function isSharedTable()
    {
        return ($this->ownerTable !== $this->getTable());
    }

    /**
     * Determines if this field if read only.
     *
     * @return    bool
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * Sets the default value for this field.
     *
     * @param    mixed $value The new default value.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the provided value cannot
     *                                        be cast for this field.
     */
    public function setDefault($value)
    {
        // Test the default value to ensure it is valid for this field.
        $this->defaultValue = $this->castValue($value);

        return $this;
    }

    /**
     * Sets a human-readable description of the field.
     *
     * @param    mixed $description       A {@link app::decibel::regional::DLabel DLabel}
     *                                    object or string, or <code>null</code>
     *                                    to remove any existing description.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setDescription($description)
    {
        $this->setLabel('description', $description);

        return $this;
    }

    /**
     * Sets information about the model that this field belongs to.
     *
     * @param    string $owner        Qualified name of the model to which the
     *                                field was added.
     * @param    string $addedBy      Qualified name of the model that added
     *                                the field, if different to the model to
     *                                which the field was added.
     *
     * @return    static
     */
    public function setModelInformation($owner, $addedBy = null)
    {
        // Check parameters.
        if (!$addedBy) {
            $addedBy = $owner;
        }
        // Store owner information.
        $this->owner = $owner;
        $this->ownerTable = DDatabaseMapper::getTableNameFor($owner);
        // Store added by information.
        $this->addedBy = $addedBy;
        $this->addedByTable = DDatabaseMapper::getTableNameFor($addedBy);

        return $this;
    }

    /**
     * Allows the user to select a null value (i.e. not any specific option).
     *
     * The value of this option should be the string that will be used to
     * describe a null value to the user (e.g. 'None Specified').
     *
     * @param    mixed $nullOption    A {@link app::decibel::regional::DLabel DLabel}
     *                                object or string, or <code>null</code>
     *                                to remove any existing null option.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setNullOption($nullOption)
    {
        $this->setLabel('nullOption', $nullOption, true);

        return $this;
    }

    /**
     * Sets whether data for this field can be modified.
     *
     * @param    bool $readOnly Whether the field data can be modified.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setReadOnly($readOnly)
    {
        $this->setBoolean('readOnly', $readOnly);

        return $this;
    }

    /**
     * Sets the required status of the field
     *
     * @param    bool $required Whether a value is required for this field.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setRequired($required)
    {
        $this->setBoolean('required', $required);

        return $this;
    }

    /**
     * Returns a DColumnDefinition object representing the database column
     * needed for storing this field or null.
     *
     * @return    DColumnDefinition
     */
    public function getDefinition()
    {
        // Non-native fields don't have a definition.
        if (!$this->isNativeField()) {
            $definition = null;
        } else {
            // Retrieve the default value for this field.
            $mapper = $this->getDatabaseMapper();
            $defaultValue = $mapper->serialize($this->defaultValue);
            // Build the definition.
            $definition = new DColumnDefinition($this->name);
            $definition->setType($this->getDataType())
                       ->setNull($this->nullOption !== null)
                       ->setDefaultValue($defaultValue);
        }

        return $definition;
    }

    /**
     * Tests the provided field name for validity.
     *
     * @param    string $name The field name to test.
     *
     * @return    void
     * @throws    DInvalidFieldNameException    If the name for the field is invalid.
     * @throws    DReservedFieldNameException    If the name for the field is reserved
     *                                        for internal purposes.
     */
    protected function validateFieldName($name)
    {
        if (in_array($name, self::$reservedFieldNames)) {
            throw new DReservedFieldNameException($name);
        }
        // Check field name is a valid database column name.
        $validator = new DIdentifierValidator();
        $errors = $validator->validate($name);
        if ($errors) {
            throw new DInvalidFieldNameException($name);
        }
    }
}
