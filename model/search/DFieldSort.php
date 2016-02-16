<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DOneToManyRelationalField;

/**
 * Defines a sort criteria using the value of a field of a model.
 *
 * An instance of this class can be passed to
 * {@link app::decibel::model::search::DBaseModelSearch::sortBy()}
 * to define the order of returned search results.
 *
 * @author        Nikolay Dimitrov
 */
class DFieldSort extends DSortCriteria
{
    /**
     * Name of the field to sort by.
     *
     * @var        DField
     */
    protected $field;

    /**
     * Created the field based sort criteria.
     *
     * @param    DField $field    The field to sort by. This cannot be an instance
     *                            of {@link app::decibel::model::field::DOneToManyRelationalField
     *                            DOneToManyRelationalField} as it doesn't make sense to sort on this field type.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If a provided parameter is invalid.
     */
    public function __construct(DField $field)
    {
        if ($field instanceof DOneToManyRelationalField) {
            throw new DInvalidParameterValueException(
                'field',
                array(__CLASS__, __FUNCTION__),
                'Cannot sort using an instance of app\\decibel\\model\\field\\DOneToManyRelationalField'
            );
        }
        $this->field = $field;
    }

    /**
     * Returns the ORDER BY clause and adds nrequired JOINs to the search.
     *
     * @note
     * The order, DESC or ASC, should NOT be returned.
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL ORDER BY clause.
     */
    public function getCriteria(DBaseModelSearch $search)
    {
        $sortOptions = $this->field->getSortOptions();
        $search->addJoins($sortOptions['join']);

        return $sortOptions['sql'];
    }

    /**
     * Returns the field for this sort criteria.
     *
     * @return    DField
     */
    public function getField()
    {
        return $this->field;
    }
}
