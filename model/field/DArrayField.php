<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\database\statement\DJoin;
use app\decibel\database\statement\DLeftJoin;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DString;

/**
 * Represents a field that can contain a list of values.
 *
 * @author        Timothy de Paris
 */
class DArrayField extends DField implements DEnumerated
{
    use DEnumeratedField;

    /**
     * The data type used to store information for linked objects fields.
     *
     * @var        string
     */
    const DATA_TYPE_ARRAY = DArrayField::class;

    /**
     * Attempts to convert the provided data into a value that
     * can be assigned to a field of this type.
     *
     * @param    mixed $value The value to cast.
     *
     * @return    mixed    The cast value
     * @throws    DInvalidFieldValueException    If the provided value cannot
     *                                        be cast for this field.
     */
    public function castValue($value)
    {
        if ($this->isNull($value)) {
            $castValue = null;
        } else {
            if (is_array($value)) {
                $castValue = $value;
            } else {
                throw new DInvalidFieldValueException($this, $value);
            }
        }

        return $castValue;
    }

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return self::DATA_TYPE_ARRAY;
    }

    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $tableSuffix A suffix to append to the table name.
     *
     * @return    string
     */
    public function getFieldSql($tableSuffix = '')
    {
        return "`decibel_model_field_darrayfield_{$this->name}{$tableSuffix}`.`value`";
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'array';
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
        $values = DString::implode(
            array_keys($this->values),
            '</code>, <code>', '</code> or <code>'
        );

        return "Array of <code>{$values}</code>";
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    string
     */
    public function getStandardDefaultValue()
    {
        return array();
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
        // @todo Can this be made more efficient, as per getSelectSql()?
        $selectSql = "(SELECT GROUP_CONCAT(`decibel_model_field_darrayfield`.`value` SEPARATOR ', ')
			FROM `decibel_model_field_darrayfield`
			JOIN `decibel_model_dmodel` AS `decibel_model_dmodel_{$this->name}`
				ON (`decibel_model_dmodel_{$this->name}`.`id`=`decibel_model_field_darrayfield`.`id`)
			WHERE `decibel_model_field_darrayfield`.`id`=`{$this->table}`.`id`
				AND `decibel_model_field_darrayfield`.`field`='{$this->name}'";
        if ($alias) {
            $selectSql .= " AS `{$alias}`";
        }

        return array(
            'join' => null,
            'sql'  => $selectSql,
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
        // Determine which table to join from based on the provided join level.
        $leftSide = $this->getJoinTable($joinFrom);
        $rightSide = "decibel_model_field_darrayfield_{$this->name}{$tableSuffix}";

        return new DLeftJoin(
            'decibel_model_field_darrayfield',
            "(`{$leftSide}`.`id`=`{$rightSide}`.`id`
				AND `{$rightSide}`.`field`='{$this->name}')",
            $rightSide
        );
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
        return empty($value);
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->exportable = false;
    }

    /**
     * Sets whether data for this field is exportable.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $exportable Whether data for this field is exportable.
     *
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @return    void
     */
    public function setExportable($exportable)
    {
        throw new DReadOnlyParameterException('exportable', $this->name);
    }

    /**
     * Converts a data value for this field to its string equivalent.
     *
     * @param    mixed $data The data to convert.
     *
     * @return    string    The string value of the data.
     */
    public function toString($data)
    {
        if ($this->nullOption !== null
            && empty($data)
        ) {
            $stringValues = array($this->nullOption);
        } else {
            if (is_string($data)) {
                $stringValues = array($data);
            } else {
                if ($this->values) {
                    $stringValues = array();
                    foreach ($data as $value) {
                        if (isset($this->values[ $value ])) {
                            $stringValues[] = $this->values[ $value ];
                        }
                    }
                } else {
                    $stringValues = $data;
                }
            }
        }

        return implode(', ', $stringValues);
    }

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
        if (isset($row[ $alias ])) {
            $row[ $alias ] = explode(',', $row[ $alias ]);
        }
    }
}
