<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\debug\DInvalidParameterValueException;

/**
 * Provides an abstracted interface to build SQL conditions
 * based on a set of criteria.
 *
 * @author    Nikolay Dimitrov
 */
abstract class DSearchCondition
{
    /**
     * Logical AND operator for merging conditions.
     *
     * @var        string
     */
    const OPERATOR_AND = ' AND ';
    /**
     * Logical OR operator for merging conditions.
     *
     * @var        string
     */
    const OPERATOR_OR = ' OR ';

    /**
     * Return the WHERE condition and adds needed JOINs to the $search
     *
     * @param    DBaseModelSearch $search Search object to use
     *
     * @return    SQL WHERE clause condition
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     */
    abstract public function getCondition(DBaseModelSearch $search);

    /**
     * Merges a list of conditions generated
     * by {@link DFieldCondition::getCondition()}
     * into a single condition.
     *
     * @param    array  $conditions   List of conditions.
     * @param    string $operator     The logical operator that will be used
     *                                to combine WHERE or HAVING clauses. One of:
     *                                - {@link DSearchCondition::OPERATOR_AND}
     *                                - {@link DSearchCondition::OPERATOR_OR}
     *
     * @return    array
     */
    protected function mergeConditions(array $conditions, $operator)
    {
        $mergedCondition = array(
            'select' => array(),
            'where'  => array(),
            'having' => array(),
        );
        foreach ($conditions as $condition) {
            $this->mergeCondition($condition, $mergedCondition);
        }
        $mergedCondition['select'] = implode(', ', $mergedCondition['select']);
        $mergedCondition['where'] = $this->mergeConditionalStatements(
            $mergedCondition['where'],
            $operator
        );
        $mergedCondition['having'] = $this->mergeConditionalStatements(
            $mergedCondition['having'],
            $operator
        );

        return $mergedCondition;
    }

    /**
     * Merges a condition.
     *
     * @param    mixed $condition       The condition to merge
     * @param    array $mergedCondition The merged conditon.
     *
     * @return    void
     */
    protected function mergeCondition($condition, array &$mergedCondition)
    {
        // Condition could be a single WHERE statement.
        if (is_string($condition)) {
            $mergedCondition['where'][] = $condition;
            // Or an array containing SELECT, WHERE and/or HAVING.
        } else {
            foreach (array_keys($mergedCondition) as $key) {
                if (isset($condition[ $key ])
                    && !in_array($condition[ $key ], $mergedCondition[ $key ])
                ) {
                    $mergedCondition[ $key ][] = $condition[ $key ];
                }
            }
        }
    }

    /**
     * Merges an array of conditional statements.
     *
     * @note
     * If the operator is {@link DSearchCondition::OPERATOR_OR} and more than
     * one statement is provided, brackets will be added to the outside
     * of the merged statements.
     *
     * @param    array  $statements   Zero or more statements to be merged.
     *                                A pointer is used to conserve memory
     *                                and the variable will not be modified.
     * @param    string $operator     The logical operator that will be used
     *                                to combine WHERE or HAVING clauses. One of:
     *                                - {@link DSearchCondition::OPERATOR_AND}
     *                                - {@link DSearchCondition::OPERATOR_OR}
     *
     * @return    string    The merged conditional statements.
     */
    protected function mergeConditionalStatements(array &$statements, $operator)
    {
        if (count($statements) > 1) {
            $merged = '(' . implode($operator, $statements) . ')';
        } else {
            $merged = implode($operator, $statements);
        }

        return $merged;
    }
}
