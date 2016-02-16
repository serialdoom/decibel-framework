<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DBooleanField;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DTextField;

/**
 * Provides information about a column.
 *
 * @author        Nikolay Dimitrov
 */
class DColumnDefinition extends DTableElementDefinition
{
    /**
     * 'Autoincrement' field name.
     *
     * @var        string
     */
    const FIELD_AUTOINCREMENT = 'autoincrement';

    /**
     * 'Name' field name.
     *
     * @var        string
     */
    const FIELD_NAME = 'name';

    /**
     * 'Null' field name.
     *
     * @var        string
     */
    const FIELD_NULL = 'null';

    /**
     * 'Size' field name.
     *
     * @var        string
     */
    const FIELD_SIZE = 'size';

    /**
     * 'Type' field name.
     *
     * @var        string
     */
    const FIELD_TYPE = 'type';

    /**
     * 'Unsigned' field name.
     *
     * @var        string
     */
    const FIELD_UNSIGNED = 'unsigned';

    /**
     * Available column types.
     *
     * @var        array
     */
    private static $columnTypes = array(
        DField::DATA_TYPE_TINYINT    => DField::DATA_TYPE_TINYINT,
        DField::DATA_TYPE_SMALLINT   => DField::DATA_TYPE_SMALLINT,
        DField::DATA_TYPE_MEDIUMINT  => DField::DATA_TYPE_MEDIUMINT,
        DField::DATA_TYPE_INT        => DField::DATA_TYPE_INT,
        DField::DATA_TYPE_BIGINT     => DField::DATA_TYPE_BIGINT,
        DField::DATA_TYPE_FLOAT      => DField::DATA_TYPE_FLOAT,
        'char'                       => 'char',
        DField::DATA_TYPE_VARCHAR    => DField::DATA_TYPE_VARCHAR,
        DField::DATA_TYPE_TEXT       => DField::DATA_TYPE_TEXT,
        DField::DATA_TYPE_MEDIUMTEXT => DField::DATA_TYPE_MEDIUMTEXT,
        DField::DATA_TYPE_LONGTEXT   => DField::DATA_TYPE_LONGTEXT,
        DField::DATA_TYPE_DATE       => DField::DATA_TYPE_DATE,
        'datetime'                   => 'datetime',
        'blob'                       => 'blob',
        'mediumblob'                 => 'mediumblob',
        'timestamp'                  => 'timestamp',
    );
    /**
     * Column types that store double values.
     *
     * @var        array
     */
    private static $doubleColumnTypes = array(
        DField::DATA_TYPE_FLOAT,
        'decimal',
        'double',
        'real',
    );
    /**
     * Column types that store integer values.
     *
     * @var        array
     */
    private static $integerColumnTypes = array(
        DField::DATA_TYPE_TINYINT,
        DField::DATA_TYPE_SMALLINT,
        DField::DATA_TYPE_MEDIUMINT,
        DField::DATA_TYPE_INT,
        DField::DATA_TYPE_BIGINT,
    );
    /**
     * Default value.
     *
     * @var        mixed
     */
    protected $defaultValue = null;

    /**
     * Creates a new {@link DColumnDefinition} object.
     *
     * @param    string           $name  Name of the column.
     * @param    DTableDefinition $table The table to which this column belongs.
     *
     * @return    static
     */
    public function __construct($name = null, DTableDefinition $table = null)
    {
        parent::__construct($table);
        $this->setName($name);
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['defaultValue'] = $this->defaultValue;

        return $debug;
    }

    /**
     * Defines fields available for this object.
     *
     * @return    void
     */
    protected function define()
    {
        $name = new DTextField(self::FIELD_NAME, 'Name');
        $name->setMaxLength(50);
        $this->addField($name);
        $type = new DEnumStringField(self::FIELD_TYPE, 'Type');
        $type->setValues(self::$columnTypes);
        $this->addField($type);
        $autoincrement = new DBooleanField(self::FIELD_AUTOINCREMENT, 'Auto-increment');
        $autoincrement->setDefault(false);
        $this->addField($autoincrement);
        $null = new DBooleanField(self::FIELD_NULL, 'Null');
        $this->addField($null);
        $unsigned = new DBooleanField(self::FIELD_UNSIGNED, 'Unsigned');
        $this->addField($unsigned);
        $size = new DIntegerField(self::FIELD_SIZE, 'Size');
        $size->setSize(2);
        $size->setUnsigned(true);
        $size->setNullOption('N/A');
        $this->addField($size);
    }

    /**
     * Returns the name of the column represented by this definition.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->getFieldValue(self::FIELD_NAME);
    }

    /**
     * Returns the type of the column represented by this definition.
     *
     * @return    string
     */
    public function getType()
    {
        return $this->getFieldValue(self::FIELD_TYPE);
    }

    /**
     * Returns the size of the column represented by this definition.
     *
     * @return    integer
     */
    public function getSize()
    {
        return $this->getFieldValue(self::FIELD_SIZE);
    }

    /**
     * Returns the default value of the column represented by this definition.
     *
     * @return    mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the null value of the column represented by this definition.
     *
     * @return    boolean
     */
    public function getNull()
    {
        return $this->getFieldValue(self::FIELD_NULL);
    }

    /**
     * Returns the unsigned value of the column represented by this definition.
     *
     * @return    boolean
     */
    public function getUnsigned()
    {
        return $this->getFieldValue(self::FIELD_UNSIGNED);
    }

    /**
     * Returns the autoincrement value of the column represented by this definition.
     *
     * @return    boolean
     */
    public function getAutoincrement()
    {
        return $this->getFieldValue(self::FIELD_AUTOINCREMENT);
    }

    /**
     * Determines if the provided column type can have a default value.
     *
     * @param    string $type The type to test.
     *
     * @return    bool
     */
    protected function canHaveDefaultValue($type)
    {
        $nonDefaultTypes = array(
            'tinytext',
            DField::DATA_TYPE_TEXT,
            DField::DATA_TYPE_MEDIUMTEXT,
            DField::DATA_TYPE_LONGTEXT,
            'blob',
        );

        return !in_array($type, $nonDefaultTypes);
    }

    /**
     * Casts a provided value to ensure it is the correct type for this column.
     *
     * @param    mixed $value The value to cast.
     *
     * @return    mixed
     */
    public function castValueForField($value)
    {
        $type = $this->getFieldValue(self::FIELD_TYPE);
        $null = $this->getFieldValue(self::FIELD_NULL);
        if ($value === null
            || ($null && $value === 'NULL')
        ) {
            $castValue = null;
        } else {
            if (in_array($type, self::$integerColumnTypes)) {
                $castValue = (int)$value;
            } else {
                if (in_array($type, self::$doubleColumnTypes)) {
                    $castValue = (float)$value;
                } else {
                    $castValue = $value;
                }
            }
        }

        return $castValue;
    }

    /**
     * Resets the values of all fields to their default value.
     *
     * @return    void
     */
    public function resetFieldValues()
    {
        parent::resetFieldValues();
        $this->defaultValue = null;
    }

    /**
     * Sets whether the field should increment automatically.
     *
     * @note
     * If autoincrement is enabled, any default value for the column
     * will be removed.
     *
     * @param    bool $autoincrement      Whether the field should increment
     *                                    automatically.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setAutoIncrement($autoincrement)
    {
        $this->setFieldValue(self::FIELD_AUTOINCREMENT, $autoincrement);
        // Auto-increment fields can't have a default value,
        // so make sure it is reset to null.
        if ($autoincrement) {
            $this->defaultValue = null;
        }

        return $this;
    }

    /**
     * Sets the default value for the column.
     *
     * @param    mixed $defaultValue The default value.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setDefaultValue($defaultValue)
    {
        $type = $this->getFieldValue(self::FIELD_TYPE);
        if ($this->canHaveDefaultValue($type)) {
            $this->defaultValue = $this->castValueForField($defaultValue);
        }

        return $this;
    }

    /**
     * Sets the name of the column.
     *
     * @param    string $name The column name.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setName($name)
    {
        $this->setFieldValue(self::FIELD_NAME, $name);

        return $this;
    }

    /**
     * Sets whether the column can contain <code>null</code> values.
     *
     * @param    bool $null       Whether the column can contain
     *                            <code>null</code> values.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setNull($null)
    {
        $this->setFieldValue(self::FIELD_NULL, $null);

        return $this;
    }

    /**
     * Sets the column size.
     *
     * @param    integer $size The column size.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setSize($size)
    {
        $this->setFieldValue(self::FIELD_SIZE, $size);

        return $this;
    }

    /**
     * Sets the column type.
     *
     * @note
     * If the type is one of <code>tinytext</code>, <code>text</code>,
     * <code>mediumtext</code> or <code>longtext</code>, any default value
     * for the column will be removed.
     *
     * @param    string $type The column type.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setType($type)
    {
        $type = strtolower($type);
        $this->setFieldValue(self::FIELD_TYPE, $type);
        if (!$this->canHaveDefaultValue($type)) {
            $this->defaultValue = null;
        }

        return $this;
    }

    /**
     * Sets whether the field should be unsigned.
     *
     * @param    bool $unsigned Whether the field should be unsigned.
     *
     * @return    static
     * @throws    DInvalidFieldValueException    If the value is invalid.
     */
    public function setUnsigned($unsigned)
    {
        $this->setFieldValue(self::FIELD_UNSIGNED, $unsigned);

        return $this;
    }
}
