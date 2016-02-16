<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Provides functionality to map a {@link DTextField} object to the database
 * via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DTextFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DTextField::class;
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
        if ($this->field->isNull($value)
            && $this->field->getNullOption() !== null
        ) {
            set_default($operator, DFieldSearch::OPERATOR_IS_NULL);
        } else {
            set_default($operator, DFieldSearch::OPERATOR_LIKE);
        }

        return parent::getConditionalSql($value, $operator, $tableSuffix);
    }

    /**
     * Restores data from serialised form in the database.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        if ($this->field->isNull($data)) {
            $unserialized = null;
            // Otherwise just cast to a string.
        } else {
            $unserialized = (string)$data;
        }

        return $unserialized;
    }
}
