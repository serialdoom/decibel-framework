<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\utility\DPersistable;

/**
 * Provides functionality to prepare a database statement for execution.
 *
 * @author    Timothy de Paris
 */
class DStatementPreparer implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Encloses a string value with quotes, if it is not a numeric value.
     *
     * @param    string $value The string value to enclose.
     *
     * @return    string
     */
    public static function encloseStringValue($value)
    {
        if (self::isNumeric($value)) {
            $enclosed = $value;
        } else {
            $enclosed = "'{$value}'";
        }

        return $enclosed;
    }

    /**
     * Replaces and escapes values for using within SQL queries.
     *
     * The following conversions will occur:
     * - Arrays will be converted into a comma separated string.
     * - Booleans will be converted into their integer equivalent.
     * - Model instances will be substitued for the instance ID.
     * - All other parameter values will be cast to a scalar value.
     *
     * @warning
     * Multi-dimensional arrays are not supported as parameter values and will
     * result in a DInvalidParameterValueException being thrown.
     * See @ref database_exceptions for further details.
     *
     * @param    mixed $value             The value to escape.
     * @param    bool  $enclose           Whether to enclose the escaped value
     *                                    with parenthesis for arrays or quotes
     *                                    for strings. Numeric values will not
     *                                    be enclosed with quotes.
     *
     * @return    string
     * @throws    DInvalidParameterValueException    If an invalid value is provided.
     */
    public function escapeValue($value, $enclose = false)
    {
        /* @var $database DDatabase */
        $database = $this->adaptee;
        // Handle array values, convert to comma seperated list.
        if (is_array($value)) {
            $value = $this->escapeArray($value, $enclose);
            // Convert boolean to an integer.
        } else {
            if (is_bool($value)) {
                $value = (string)((int)$value);
                // Convert a model instance to it's ID.
            } else {
                if (is_object($value)
                    && $value instanceof DPersistable
                ) {
                    $value = $value->getId();
                    // Escape the value and enclode with quotes if applicable.
                } else {
                    $value = $database->escape((string)$value);
                    if ($enclose) {
                        $value = self::encloseStringValue($value);
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Escapes array values for use within SQL queries.
     *
     * @warning
     * Multi-dimensional arrays are not supported as parameter values and will
     * result in a {@link DInvalidParameterValueException} being thrown.
     * See @ref database_exceptions for further details.
     *
     * @param    array $value             The value to escape.
     * @param    bool  $enclose           Whether to enclose the escaped value
     *                                    with parenthesis.
     *
     * @return    string
     * @throws    DInvalidParameterValueException    If an invalid value is provided.
     */
    protected function escapeArray(array $value, $enclose)
    {
        if (count($value) === 0) {
            $value = 'false';
            // Recursively escape array values.
        } else {
            $values = array();
            foreach ($value as $val) {
                // Can't handle multi-dimensional arrays!
                if (is_array($val)) {
                    throw new DInvalidParameterValueException('unknown');
                }
                $values[] = $this->escapeValue($val, true);
            }
            $value = implode(',', $values);
        }
        if ($enclose) {
            $enclosed = "({$value})";
        } else {
            $enclosed = $value;
        }

        return $enclosed;
    }

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DDatabase::class;
    }

    /**
     * Determines if the provided mixed type variable represent a numeric value.
     *
     * @param    mixed $value
     *
     * @return    bool
     */
    protected static function isNumeric($value)
    {
        return ($value === '0'
            || $value === 0
            || preg_match('/^[1-9][0-9]*$/', $value));
    }

    /**
     * Emmulates stored procedures by substituting parameters into the query
     * before executing it.
     *
     * @param    string $sql    The sql statement.
     * @param    array  $params The parameters to substitute into the query.
     *
     * @return    string
     * @throws    DInvalidParameterValueException    If an invalid value is provided.
     */
    public function prepare($sql, array &$params)
    {
        // Handle the case where one of the parameter values contains
        // a valid parameter placeholder (i.e. #parameterName#)
        // Generate a random string that doesn't exist in any of the parameter
        // values. All hash characters will be replaced by the random string,
        // before being returned to hash characters in the final SQL.
        $values = serialize($params);
        do {
            $rand = md5(time() . rand());
        } while (strpos($values, $rand) !== false);
        // Substitute variables.
        $find = array();
        $replace = array();
        foreach ($params as $name => &$value) {
            $this->substituteParam($name, $value, $rand, $find, $replace);
        }
        // Finally return the hash characters.
        $find[] = $rand;
        $replace[] = '#';

        // Replace all values.
        return str_replace($find, $replace, $sql);
    }

    /**
     * Prepares for the substitution of a variable within a query.
     *
     * @param    string $name         Name of the variable.
     * @param    mixed  $value        Value of the variable.
     * @param    string $rand         Random hash for replacement of placeholders
     *                                within variable values.
     * @param    array  $find         Pointer to the list of searches
     *                                to be performed.
     * @param    array  $replace      Pointer to the list of associated
     *                                replacements.
     *
     * @return    void
     * @throws    DInvalidParameterValueException
     */
    protected function substituteParam($name, $value, $rand, array &$find, array &$replace)
    {
        // Escape the value.
        if ($value !== null) {
            try {
                $value = $this->escapeValue($value, false);
            } catch (DInvalidParameterValueException $exception) {
                throw new DInvalidParameterValueException($name);
            }
        }
        // Automatically remove quotes from numeric values.
        if ($value === null
            || self::isNumeric($value)
        ) {
            if ($value === null) {
                $value = 'NULL';
            }
            $find[] = "'#{$name}#'";
            $find[] = "#{$name}#";
            $replace[] = $value;
            $replace[] = $value;
        } else {
            // Replace hash characters in the value.
            $find[] = "#{$name}#";
            $replace[] = str_replace('#', $rand, $value);
        }
    }
}
