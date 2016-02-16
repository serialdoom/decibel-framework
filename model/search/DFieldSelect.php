<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DOneToManyRelationalField;
use app\decibel\model\search\DSelect;

/**
 * Defines a select on a field of a model.
 *
 * @author        Timothy de Paris
 */
class DFieldSelect extends DSelect
{
    /**
     * Name of the field to select or chain of field names.
     *
     * @var        array
     */
    public $fieldName;
    /**
     * Name of the (final) field to select.
     *
     * @var        array
     */
    public $finalFieldName;

    /**
     * Created the field based sort criteria.
     *
     * @param    mixed  $fieldName    The field to include in returned results.
     *                                Could be an array with a chain of linked fields
     *                                where the last element is the name of the actual
     *                                field to select.
     * @param    string $function     The aggregate function to apply to the field.
     *                                One of:
     *                                - {@link DBaseModelSearch::AGGREGATE_NONE}
     *                                - {@link DBaseModelSearch::AGGREGATE_MAX}
     *                                - {@link DBaseModelSearch::AGGREGATE_MIN}
     *                                - {@link DBaseModelSearch::AGGREGATE_AVG}
     *                                - {@link DBaseModelSearch::AGGREGATE_SUM}
     *                                - {@link DBaseModelSearch::AGGREGATE_COUNT}
     * @param    string $returnType   How field values will be returned. One of:
     *                                - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *                                If not provided, the value of the $returnType
     *                                parameter passed to the result retrieval
     *                                function ({@link DBaseModelSearch::getField()}
     *                                or {@link DBaseModelSearch::getFields()}
     *                                will be used.
     * @param    string $alias        Name of the field in returned results.
     *                                If not provided, the field name will be used.
     *
     * @return    static
     */
    public function __construct($fieldName,
                                $function = DBaseModelSearch::AGGREGATE_NONE,
                                $returnType = null, $alias = null)
    {
        parent::__construct();
        if (!is_array($fieldName)) {
            $fieldName = array($fieldName);
        }
        $this->fieldName = $fieldName;
        $this->finalFieldName = $fieldName[ count($fieldName) - 1 ];
        $this->aggregateFunction = $function;
        $this->returnType = $returnType;
        $this->alias = $alias;
    }

    /**
     * Adds joins to the search for this select and returns
     * the appropriate SELECT SQL.
     *
     * @param    DBaseModelSearch $search            The search object.
     * @param    DField           $field             The field to return.
     * @param    array            $joins             Joins to be added.
     * @param    string           $defaultReturnType How field values will be returned. One of:
     *                                               - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *                                               If not provided, the value of the
     *                                               <code>$returnType</code> parameter
     *                                               passed to the constructor will be used.
     * @param    string           $aliasSuffix       A suffix to append to the table name.
     *
     * @return    string
     */
    protected function addJoins(DBaseModelSearch $search, DField $field,
                                array $joins, $defaultReturnType = null, $aliasSuffix = null)
    {
        // Determine return type to use.
        $returnType = $this->returnType
            ? $this->returnType
            : $defaultReturnType;
        $search->addJoins($joins);
        // Determine field alias.
        $fieldName = $this->getFieldName();
        // Handle aggregate functions.
        if ($this->aggregateFunction !== DBaseModelSearch::AGGREGATE_NONE) {
            $select = "{$this->aggregateFunction}({$field->getSelectSql(null, $aliasSuffix)}) AS `{$fieldName}`";
            // Use "string value" SQL if this is the specified return type
            // Include deprecated option of "true"
        } else {
            if ($returnType === DBaseModelSearch::RETURN_STRING_VALUES) {
                $stringSelect = $field->getStringValueSql($fieldName, $aliasSuffix);
                if ($stringSelect['join']) {
                    $search->addJoins($stringSelect['join']);
                }
                $select = $stringSelect['sql'];
                // Or finally, just a simple select!
            } else {
                $select = $field->getSelectSql($fieldName, $aliasSuffix);
            }
        }

        return $select;
    }

    /**
     * Returns the chain of field names represented by this select.
     *
     * @return    array
     */
    public function getFieldChain()
    {
        return $this->fieldName;
    }

    /**
     * Returns the name of this field, under which values will be returned
     * in the result set.
     *
     * @return    string
     */
    public function getFieldName()
    {
        if ($this->alias) {
            $fieldName = $this->alias;
        } else {
            $fieldName = $this->finalFieldName;
        }

        return $fieldName;
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
     * Returns the SELECT clause and adds required JOINs to the search.
     *
     * @param    DBaseModelSearch $search            Search object to use.
     * @param    string           $defaultReturnType How values will be returned
     *                                               if not explicitly specified for
     *                                               this select. One of:
     *                                               - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *
     * @return    SQL SELECT clause.
     */
    public function getSelect(DBaseModelSearch $search,
                              $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED)
    {
        $aliasSuffix = '';
        $joins = array();
        $fields = $search->getChainedFields($this->fieldName);
        $fieldCount = count($fields);
        for ($i = 0; $i < $fieldCount; ++$i) {
            // Add an initial join for the first field, in case it has
            // been added by an ancestor of the model.
            if ($i === 0) {
                $lastJoin = $fields[ $i ]->getJoin();
                $joins[] = $lastJoin;
            }
            // For pairs of chained fields, prepare appropriate joins.
            if ($i < ($fieldCount - 1)) {
                $from = $fields[ $i ];
                $to = $fields[ $i + 1 ];
                // Select a letter to be used when aliasing joins.
                $alias = $this->getJoinAlias($search, $fields);
                $aliasSuffix = "_{$alias}{$i}_{$from->getName()}";
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
        }
        // If this is the last field, add all the joins
        // and return the SQL WHERE condition.
        return $this->addJoins(
            $search,
            array_pop($fields),
            $joins,
            $defaultReturnType,
            $aliasSuffix
        );
    }

    /**
     * Processes a row of data containing the value for this select
     * based on the required return type.
     *
     * @param    DBaseModelSearch $search            Search object to use.
     * @param    mixed            $row               Pointer to the row to be processed.
     * @param    string           $defaultReturnType How values will be returned
     *                                               if not explicitly specified for
     *                                               this select. One of:
     *                                               - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                                               - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *
     * @return    void
     */
    public function processRow(DBaseModelSearch $search, &$row,
                               $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED)
    {
        /* @var $field DField */
        $chainedFields = $search->getChainedFields($this->fieldName);
        $fieldName = $this->getFieldName();
        $field = array_pop($chainedFields);
        // Determine return type to use.
        $returnType = $this->returnType
            ? $this->returnType
            : $defaultReturnType;
        switch ($returnType) {
            case DBaseModelSearch::RETURN_STRING_VALUES:
                $this->processRowString($field, $fieldName, $row);
                break;
            case DBaseModelSearch::RETURN_UNSERIALIZED:
                $this->processRowUnserialized($field, $fieldName, $row);
                break;
            case DBaseModelSearch::RETURN_SERIALIZED:
            default:
                $this->processRowSerialized($field, $fieldName, $row);
                break;
        }
    }

    /**
     * Processes a row to return serialized values.
     *
     * @param    DField $field
     * @param    string $fieldName
     * @param    array  $row
     *
     * @return    void
     */
    protected function processRowSerialized(DField $field, $fieldName, array &$row)
    {
        try {
            $field->processRow($row, $fieldName);
            $row[ $fieldName ] = $field->serialize($field->castValue($row[ $fieldName ]));
        } catch (DInvalidFieldValueException $exception) {
        }
    }

    /**
     * Processes a row to return string values.
     *
     * @param    DField $field
     * @param    string $fieldName
     * @param    array  $row
     *
     * @return    void
     */
    protected function processRowString(DField $field, $fieldName, array &$row)
    {
        if (isset($row[ $fieldName ])) {
            $row[ $fieldName ] = $field->toString($row[ $fieldName ]);
        }
    }

    /**
     * Processes a row to return unserialized values.
     *
     * @param    DField $field
     * @param    string $fieldName
     * @param    array  $row
     *
     * @return    void
     */
    protected function processRowUnserialized(DField $field, $fieldName, array &$row)
    {
        // Process the row before unserializing, to ensure
        // fields with multiple values are combined first.
        $field->processRow($row, $fieldName);
        if (isset($row[ $fieldName ])) {
            $mapper = $field->getDatabaseMapper();
            $row[ $fieldName ] = $mapper->unserialize($row[ $fieldName ]);
        }
    }
}
