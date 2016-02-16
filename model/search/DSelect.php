<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

/**
 * Provides an interface to build included fields for DBaseModelSearch queries.
 *
 * @author        Timothy de Paris
 */
abstract class DSelect
{
    /**
     * Aggregate function for this select.
     *
     * One of:
     * - {@link DBaseModelSearch::AGGREGATE_NONE}
     * - {@link DBaseModelSearch::AGGREGATE_MAX}
     * - {@link DBaseModelSearch::AGGREGATE_MIN}
     * - {@link DBaseModelSearch::AGGREGATE_AVG}
     * - {@link DBaseModelSearch::AGGREGATE_SUM}
     * - {@link DBaseModelSearch::AGGREGATE_COUNT}
     *
     * @var        string
     */
    protected $aggregateFunction;
    /**
     * Alias for returned field values.
     *
     * @var        string
     */
    protected $alias;
    /**
     * Return type for values of this field.
     *
     * One of:
     * - {@link DBaseModelSearch::RETURN_SERIALIZED}
     * - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     * - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     *
     * @var        string
     */
    protected $returnType;

    /**
     * Creates a new {@link DSelect} object.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->aggregateFunction = DBaseModelSearch::AGGREGATE_NONE;
    }

    /**
     * Returns the aggregate function for this select.
     *
     * @return    string    One of:
     *                    - {@link DBaseModelSearch::AGGREGATE_NONE}
     *                    - {@link DBaseModelSearch::AGGREGATE_MAX}
     *                    - {@link DBaseModelSearch::AGGREGATE_MIN}
     *                    - {@link DBaseModelSearch::AGGREGATE_AVG}
     *                    - {@link DBaseModelSearch::AGGREGATE_SUM}
     *                    - {@link DBaseModelSearch::AGGREGATE_COUNT}
     */
    public function getAggregateFunction()
    {
        return $this->aggregateFunction;
    }

    /**
     * Returns the alias for this select.
     *
     * @return    string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Returns a caching ID for this field.
     *
     * @return    string
     */
    public function getCacheId()
    {
        $cacheId = $this->getFieldName();
        // Append return type if explicitly defined for this select.
        if ($this->returnType) {
            $cacheId .= '|' . $this->returnType;
        }
        // Append aggregate function if available.
        if ($this->aggregateFunction !== DBaseModelSearch::AGGREGATE_NONE) {
            $cacheId .= '|' . $this->aggregateFunction;
        }

        return $cacheId;
    }

    /**
     * Returns the name of this field, under which values will be returned
     * in the result set.
     *
     * @return    string
     */
    abstract public function getFieldName();

    /**
     * Returns the return type for this select.
     *
     * @return    string    One of:
     *                    - {@link DBaseModelSearch::RETURN_SERIALIZED}
     *                    - {@link DBaseModelSearch::RETURN_UNSERIALIZED}
     *                    - {@link DBaseModelSearch::RETURN_STRING_VALUES}
     */
    public function getReturnType()
    {
        return $this->returnType;
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
    abstract public function getSelect(DBaseModelSearch $search,
                                       $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED);

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
    abstract public function processRow(DBaseModelSearch $search, &$row,
                                        $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED);
}
