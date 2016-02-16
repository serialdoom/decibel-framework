<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\database\DQuery;
use app\decibel\database\statement\DJoin;
use app\decibel\utility\DPersistable;

/**
 * Provides functionality to map a {@link app::decibel::model::field::DField DField}
 * object to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DFieldDatabaseMapper implements DAdapter
{
    use DRuntimeAdapter;
    /**
     * The field being mapped to the database.
     *
     * @var        DField
     */
    protected $field;
    /**
     * The name of the field being mapped.
     *
     * @var        string
     */
    protected $fieldName;

    /**
     * Creates a new {@link DFieldDatabaseMapper} object.
     *
     * @param    DField $field The field being mapped to the database.
     *
     * @return    DFieldDatabaseMapper
     */
    protected function __construct(DField $field)
    {
        $this->adaptee = $field;
        $this->field = $field;
        $this->fieldName = $field->getName();
    }

    /**
     * Implements any functionality required to remove extraneous records
     * from the database associated with this field type.
     *
     * Triggered by {@link app::decibel::database::maintenance::DOptimiseDatabase DOptimiseDatabase}.
     *
     * @return    DResult    The result of the operation, or <code>null</code>
     *                    if no action was performed.
     */
    public static function cleanDatabase()
    {
        return null;
    }

    /**
     * Called when deleting {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @note
     * This method should be overriden in inheriting classes which
     * load data from other database tables.
     *
     * @param    DPersistable $instance The model instance being deleted.
     *
     * @return    bool    <code>true</code> if data was deleted,
     *                    <code>false</code> if not.
     */
    public function delete(DPersistable $instance)
    {
        return false;
    }

    /**
     * Ensures the operator is correct for a null search.
     *
     * @param    string $operator
     *
     * @return    string
     */
    protected function fixNullOperator($operator)
    {
        if ($operator === DFieldSearch::OPERATOR_EQUAL) {
            $fixedOperator = DFieldSearch::OPERATOR_IS_NULL;
        } else {
            if ($operator === DFieldSearch::OPERATOR_NOT_EQUAL) {
                $fixedOperator = DFieldSearch::OPERATOR_NOT_NULL;
                // No changes required for any other operators.
            } else {
                $fixedOperator = $operator;
            }
        }

        return $fixedOperator;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DField::class;
    }

    /**
     * Returns the required SQL to perform a search on this field.
     *
     * @param    mixed  $value            The value to search for.
     * @param    string $operator         The operator to use. If not provided, the
     *                                    default operator for this field will be used.
     * @param    string $tableSuffix      A suffix to append to the table name.
     *
     * @return    string
     */
    public function getConditionalSql($value, $operator = null, $tableSuffix = '')
    {
        $operator = $this->getConditionalSqlOperator($value, $operator);
        $fieldSql = $this->field->getFieldSql($tableSuffix);
        // Prepare the value for the search.
        $this->prepareConditionalSqlValue($value, $operator);
        // Special case for NULL operators.
        if ($operator === DFieldSearch::OPERATOR_IS_NULL
            || $operator === DFieldSearch::OPERATOR_NOT_NULL
        ) {
            $sql = "{$fieldSql} {$operator}";
            // Special case of NULL fields with an IN search.
        } else {
            if ($operator === DFieldSearch::OPERATOR_IN
                && $this->field->getNullOption() !== null
            ) {
                $sql = "({$fieldSql} {$operator} {$value} AND {$fieldSql} IS NOT NULL)";
                // Special case of NULL fields with a NOT IN search.
            } else {
                if ($operator === DFieldSearch::OPERATOR_NOT_IN
                    && $this->field->getNullOption() !== null
                ) {
                    $sql = "({$fieldSql} {$operator} {$value} OR {$fieldSql} IS NULL)";
                } else {
                    $sql = "{$fieldSql} {$operator} {$value}";
                }
            }
        }

        return $sql;
    }

    /**
     * Selects the appropriate operator for the provided value.
     *
     * @param    mixed  $value
     * @param    string $operator
     *
     * @return    string
     */
    protected function getConditionalSqlOperator(&$value, $operator = null)
    {
        // Determine the default operator to use, if not provided.
        if (is_array($value)) {
            set_default($operator, DFieldSearch::OPERATOR_IN);
        } else {
            set_default($operator, DFieldSearch::OPERATOR_EQUAL);
        }
        $field = $this->field;
        if ($field->isNull($value)
            && $field->getNullOption() !== null
        ) {
            $operator = $this->fixNullOperator($operator);
        }

        return $operator;
    }

    /**
     * Returns the field being mapped to the database.
     *
     * @return    DField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Returns the name of the field being mapped to the database.
     *
     * @return    string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Returns the name of the table to be joined from based on the provided
     * qualified name from the hierarchy of the field's owner.
     *
     * @param    DJoin $joinFrom      A {@link app::decibel::database::statement::DJoin DJoin}
     *                                object representing the left side of this
     *                                join. If not provided, the lowest level
     *                                of the model hierarchy will be joined from.
     *
     * @return    string
     */
    protected function getJoinTable(DJoin $joinFrom = null)
    {
        // Determine which table to join from based
        // on the provided join level.
        if ($joinFrom === null) {
            $table = $this->field->getTable();
        } else {
            $table = $joinFrom->getAlias();
        }

        return $table;
    }

    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $alias            If provided, this alias will be applied
     *                                    to the field in the returned SQL.
     * @param    string $tableSuffix      A suffix to append to the table name.
     *
     * @return    string
     */
    public function getSelectSql($alias = null, $tableSuffix = '')
    {
        $fieldName = $this->fieldName;
        $table = $this->field->getTable();
        if ($table) {
            $sql = "`{$table}{$tableSuffix}`.`{$fieldName}`";
        } else {
            $sql = "`{$fieldName}`";
        }
        if ($alias !== null
            && $alias !== $fieldName
        ) {
            $sql .= " AS `{$alias}`";
        }

        return $sql;
    }

    /**
     * Called when loading {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @note
     * This method should be overriden in inheriting classes which
     * load data from other database tables.
     *
     * @param    DPersistable $instance       The model instance being loaded.
     * @param    array        $data           Pointer to the data array for
     *                                        the model instance.
     *
     * @return    bool    <code>true</code> if data was loaded,
     *                    <code>false</code> if not.
     */
    public function load(DPersistable $instance, array &$data)
    {
        return false;
    }

    /**
     * Prepares and normalises a value to be used in a conditional search.
     *
     * @param    mixed  $value
     * @param    string $operator The search operation being performed.
     *
     * @return    void
     */
    protected function prepareConditionalSqlValue(&$value, $operator)
    {
        // Is this neccessary?
        if ($operator === DFieldSearch::OPERATOR_LIKE) {
            $value = addcslashes($value, '\\');
        }
        if (($operator === DFieldSearch::OPERATOR_IN
            || $operator === DFieldSearch::OPERATOR_NOT_IN)
        ) {
            $value = (array)$value;
        }
        // Escape and quote the value.
        $value = DQuery::escapeValue($value, true);
    }

    /**
     * Called when saving {@link app::decibel::utility::DPersistable DPersistable}
     * instances for fields which are not native (that is, data is not stored
     * in the model's own table).
     *
     * @note
     * This method should be overriden in inheriting classes which
     * save data to other database tables.
     *
     * @param    DPersistable $instance The model instance being saved.
     *
     * @return    bool    <code>true</code> if data was saved,
     *                    <code>false</code> if not.
     */
    public function save(DPersistable $instance)
    {
        return false;
    }

    /**
     * Prepares field data for saving to the database.
     *
     * By default, this method converts data to the internal data type
     * returned by the {@link DField::getInternalDataType()} method.
     * This should be overriden for fields that have non-native internal
     * data types.
     *
     * @param    mixed $data The data to serialize.
     *
     * @return    mixed    The serialized data.
     */
    public function serialize($data)
    {
        if ($this->field->isNull($data)) {
            $data = null;
            // Cast the data and return.
        } else {
            settype($data, $this->field->getInternalDataType());
        }

        return $data;
    }

    /**
     * Restores data from serialised form in the database.
     *
     * By default, this method converts data to the internal data type
     * returned by the {@link DField::getInternalDataType()} method.
     * This should be overriden for fields that have non-native internal
     * data types.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        if ($this->field->isNull($data)) {
            $data = null;
            // Cast the data and return.
        } else {
            settype($data, $this->field->getInternalDataType());
        }

        return $data;
    }
}
