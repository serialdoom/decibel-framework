<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\search;

use app\decibel\application\DClassManager;
use app\decibel\database\statement\DLeftJoin;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\DLightModel;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DLinkedObjectField;

/**
 * Defines a sort on a field of an object linked to a model.
 *
 * @author        Timothy de Paris
 */
class DLinkedFieldSort extends DFieldSort
{
    /**
     * The field that links to the model containing the field to be returned.
     *
     * @var        DLinkedObjectField
     */
    private $linkField;

    /**
     * Creates a new {@link DLinkedFieldSort} object.
     *
     * @param    DLinkedObjectField $linkField    The field that links
     *                                            to the model containing
     *                                            the field to be returned.
     * @param    DField             $field        The field in the linked model to sort results
     *                                            by. This cannot    be an instance of
     *                                            {@link app::decibel::model::field::DOneToManyRelationalField
     *                                            DOneToManyRelationalField} as it doesn't make sense to sort on this
     *                                            field type.
     *
     * @return    static
     */
    public function __construct(DLinkedObjectField $linkField, DField $field)
    {
        parent::__construct($field);
        $this->linkField = $linkField;
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
        // Determine field information.
        $linkField = $this->linkField;
        $linkTo = $linkField->linkTo;
        $linkFieldName = $linkField->name;
        $field = $this->field;
        $fieldName = $this->field->name;
        // Determine if a join is required to access the linked field.
        // This will be the case if it is added by an ancestor of the model.
        $linkFieldJoin = $linkField->getJoin();
        $linkFieldAlias = $linkFieldJoin
            ? $linkFieldJoin->getAlias()
            : $linkField->table;
        // For light models, join directly to the table, as there is no hierarchy.
        if (DClassManager::isValidClassName($linkTo, DLightModel::class)) {
            // Join to the linked object.
            $linkTableName = DDatabaseMapper::getTableNameFor($linkTo);
            // Use the table name as the alias so that multiple selects of fields from
            // this table don't generate additional joins.
            $linkedObjectAlias = $linkTableName;
            $linkedObjectJoin = new DLeftJoin(
                $linkTableName,
                "(`{$linkFieldAlias}`.`{$linkFieldName}`=`{$linkedObjectAlias}`.`id`)",
                $linkedObjectAlias
            );
            $linkedFieldAlias = null;
            // For other models, join to the top of the hierarchy.
        } else {
            // Join to the linked object.
            $modelTable = DDatabaseMapper::getTableNameFor(DModel::class);
            $linkedObjectAlias = "{$modelTable}_{$linkFieldName}";
            $linkedObjectJoin = new DLeftJoin(
                $modelTable,
                "(`{$linkFieldAlias}`.`{$linkFieldName}`=`{$linkedObjectAlias}`.`id`)",
                $linkedObjectAlias
            );
            // Join to the field to be returned within the linked object.
            $linkedFieldAlias = "_{$linkFieldName}_{$fieldName}";
        }
        // Add the joins to the search.
        $search->addJoins(array(
                              $linkFieldJoin,
                              $linkedObjectJoin,
                          ));
        // Join to the linked field.
        $linkedFieldJoin = $field->getJoin($linkedFieldAlias, $linkedObjectJoin);
        if ($linkedFieldJoin !== null) {
            $search->addJoin($linkedFieldJoin);
        }

        // Or finally, just a "simple" select!
        return $field->getSelectSql(null, $linkedFieldAlias);
    }
}
