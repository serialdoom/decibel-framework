<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Provides functionality to map a {@link DDateTimeField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DDateTimeFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DDateTimeField::class;
    }

    /**
     * Returns the required SQL to perform a search on this field.
     *
     * @param    mixed  $value        The value to search for.
     * @param    string $operator     The operator to use. If not provided, the
     *                                default operator for this field will be used.
     * @param    string $tableSuffix  A suffix to append to the table name.
     *
     * @return    string
     */
    public function getConditionalSql($value, $operator = null, $tableSuffix = '')
    {
        // Determine operator to use.
        set_default($operator, DFieldSearch::OPERATOR_EQUAL);
        if ($value === null) {
            $sql = parent::getConditionalSql($value, $operator, $tableSuffix);
            // Handle timestamps.
        } else {
            if (is_numeric($value)) {
                $sql = "{$this->field->getFieldSql()} {$operator} {$value}";
                // Handle date range.
            } else {
                if ($operator == DFieldSearch::OPERATOR_EQUAL) {
                    $from = strtotime($value);
                    $to = strtotime($value) + 86399;
                    $sql = "({$this->field->getFieldSql()} BETWEEN {$from} AND {$to})";
                    // Or single date.
                } else {
                    $value = strtotime($value);
                    $sql = "{$this->field->getFieldSql()} {$operator} {$value}";
                }
            }
        }

        return $sql;
    }
}
