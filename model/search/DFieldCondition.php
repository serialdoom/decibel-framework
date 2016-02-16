<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\database\statement\DJoin;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\field\DChildObjectsField;
use app\decibel\model\field\DField;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DOneToManyRelationalField;

/**
 * Provides filtering based on the value of a field within a model.
 *
 * @author    Timothy de Paris
 */
class DFieldCondition extends DSearchCondition
{
    /**
     * The field name or chain of field names on which the search
     * is taking place.
     *
     * @var        mixed
     */
    protected $fieldName;
    /**
     * The search value.
     *
     * @var        mixed
     */
    protected $value;
    /**
     * The search operator.
     *
     * @var        mixed
     */
    protected $operator;

    /**
     * Set the fields or indexes to be used for searching.
     *
     * @param    mixed  $fieldName    Name of the field to search on, or a list
     *                                of chained fields.
     * @param    mixed  $value        Search value.
     * @param    string $operator     The operator use with the object. If not provided
     *                                the default operator for the field type will be used.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     */
    public function __construct($fieldName, $value, $operator = null)
    {
        $this->fieldName = $fieldName;
        $this->operator = $operator;
        $this->setValue($value);
    }

    /**
     * Determines a unique alias for the joins for this condition, if needed.
     *
     * Two or more AND clauses on the same field in a one-to-many
     * relationship, anywhere in the chain, needs multiple JOINs.
     *
     * For example,
     * A recipe has multiple ingredients. If I want to find all recipes
     * with "flour" AND "butter", multiple joins are needed. If I want
     * recipes with "flour" OR "butter", only one join is needed.
     *
     * @param    DBaseModelSearch $search
     * @param    array            $fields Chain of {@link DField} objects.
     *
     * @return    string
     */
    protected function getJoinAlias(DBaseModelSearch $search, array $fields)
    {
        $alias = '';
        foreach ($fields as $field) {
            /* @var $field DField */
            if ($field instanceof DOneToManyRelationalField) {
                $alias = $search->getNextConditionKey() . '_';
                break;
            }
        }

        return $alias;
    }

    /**
     * Return the WHERE condition and adds needed JOINs to the search.
     *
     * @param    DBaseModelSearch $search
     *
     * @return    SQL WHERE clause condition
     */
    public function getCondition(DBaseModelSearch $search)
    {
        $fields = $search->getChainedFields($this->fieldName);
        // Select a letter to be used when aliasing joins.
        $alias = $this->getJoinAlias($search, $fields);
        $aliasSuffix = '';
        $joins = array();
        $lastJoin = null;
        $fieldCount = count($fields);
        for ($i = 0; $i < $fieldCount; ++$i) {
            // Add an initial join for the first field, in case it has
            // been added by an ancestor of the model.
            if ($i === 0) {
                $this->addInitialJoin($fields[ $i ], $alias, $aliasSuffix, $joins, $lastJoin);
            }
            // For pairs of chained fields, prepare appropriate joins.
            if ($i < $fieldCount - 1) {
                $aliasSuffix = "_{$alias}{$i}_{$fields[$i]->getName()}";
                $this->addJoin($search, $fields[ $i ], $fields[ $i + 1 ],
                               $aliasSuffix, $joins, $lastJoin);
            }
            // If this is the last field, add all the joins
            // and return the SQL WHERE condition.
            if ($i === $fieldCount - 1) {
                $search->addJoins($joins);

                return $this->getConditions($fields[ $i ], $aliasSuffix);
            }
        }
    }

    /**
     *
     * @param    DField $field
     * @param    string $alias
     * @param    string $aliasSuffix
     * @param    array  $joins
     * @param    DJoin  $lastJoin
     *
     * @return    void
     */
    protected function addInitialJoin(DField $field, $alias, &$aliasSuffix,
                                      array &$joins, DJoin &$lastJoin = null)
    {
        if ($field instanceof DChildObjectsField) {
            $lastJoin = null;
        } else {
            // Native fields are never aliased.
            if ($field->isNativeField()) {
                $aliasSuffix = '';
                // All others are to allows AND conditions
                // on joins to the same table.
            } else {
                $aliasSuffix = "_{$alias}{$field->getName()}";
            }
            $lastJoin = $field->getJoin($aliasSuffix);
            $joins[] = $lastJoin;
        }
    }

    /**
     *
     * @param    DBaseModelSearch $search
     * @param    DField           $from
     * @param    DField           $to
     * @param    string           $aliasSuffix
     * @param    array            $joins
     * @param    DJoin            $lastJoin
     *
     * @return    void
     */
    protected function addJoin(DBaseModelSearch $search, DField $from, DField $to,
                               $aliasSuffix, array &$joins, DJoin &$lastJoin)
    {
        if ($lastJoin) {
            $fromAlias = $lastJoin->getAlias();
        } else {
            $model = $search->qualifiedName;
            $fromAlias = DDatabaseMapper::getTableNameFor($model);
        }
        $modelJoin = $from->getJoinTo($to, $fromAlias, $aliasSuffix);
        $joins[] = $modelJoin;
        $fieldJoin = $to->getJoin($aliasSuffix, $modelJoin);
        $joins[] = $fieldJoin;
        $lastJoin = $fieldJoin;
    }

    /**
     * Returns the SQL WHERE condition.
     *
     * @param    DField $field       The field on which the search is taking place.
     * @param    string $aliasSuffix Suffix for this part of the search.
     *
     * @return    string
     */
    protected function getConditions(DField $field, $aliasSuffix)
    {
        $mapper = $field->getDatabaseMapper();

        return $mapper->getConditionalSql(
            $this->value,
            $this->operator,
            $aliasSuffix
        );
    }

    /**
     * Determines if this condition contains the specified field.
     *
     * @param    string $fieldName Name of the field.
     *
     * @return    bool
     */
    public function includesField($fieldName)
    {
        // Test for both single field name or array of field names.
        return $this->fieldName === $fieldName
        || (is_array($this->fieldName)
            && in_array($fieldName, $this->fieldName));
    }

    /**
     * Sets the search operator.
     *
     * @param    string $operator     The operator use with the object. If not provided
     *                                the default operator for the field type will be used.
     *
     * @return    void
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * Sets the field or chain of fields on which the search will be performed.
     *
     * @param    mixed $fieldName     Name of the field to search on, or a list
     *                                of chained fields.
     *
     * @return    void
     */
    public function setField($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Normalises and sets the search value for this condition.
     *
     * @param    string $value Search value.
     *
     * @return    void
     */
    public function setValue(&$value)
    {
        // Convert objects to IDs in the search value.
        if (is_object($value)) {
            $this->value = $value->getId();
        } else {
            if (is_array($value)) {
                $this->setValueArray($value);
            } else {
                $this->value = $value;
            }
        }
    }

    /**
     * Normalises and sets an array search value for this condition.
     *
     * @param    array $value Search value.
     *
     * @return    void
     */
    protected function setValueArray(array &$value)
    {
        foreach ($value as &$subValue) {
            if (is_object($subValue)) {
                $subValue = $subValue->getId();
            }
        }
        $this->value = $value;
        // If the provided value is an array, this must be an IN search.
        if ($this->operator === null) {
            $this->operator = DFieldSearch::OPERATOR_IN;
        }
    }
}
