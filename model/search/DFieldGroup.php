<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DOneToManyRelationalField;

/**
 * Defines a group criteria on a field of a model.
 *
 * An instance of this class can be passed to
 * {@link app::decibel::model::search::DBaseModelSearch::groupBy()}
 * to define the grouping of returned search results.
 *
 * @author    Sajid Afzal
 */
class DFieldGroup extends DGroupCriteria
{
    /**
     * Name of the field to sort by.
     *
     * @var        DField
     */
    private $field;

    /**
     * Created the field based group criteria.
     *
     * @param    DField $field The field to sort by.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the provided field cannot
     *                                            be used to group the result
     *                                            of a model search.
     */
    public function __construct(DField $field)
    {
        if ($field instanceof DOneToManyRelationalField) {
            throw new DInvalidParameterValueException(
                'field',
                array(__CLASS__, __FUNCTION__),
                'Cannot group using an instance of app\\decibel\\model\\field\\DOneToManyRelationalField'
            );
        }
        $this->field = $field;
    }

    /**
     * Returns the GROUP BY clause and adds required JOINs to the search.
     *
     * @param    DBaseModelSearch $search Search object to use.
     *
     * @return    SQL GROUP BY clause.
     */
    public function getCriteria(DBaseModelSearch $search)
    {
        $join = $this->field->getJoin();
        if ($join) {
            $search->addJoin($join);
        }

        return $this->field->getFieldSql();
    }
}
