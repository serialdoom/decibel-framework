<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\field;

use app\decibel\application\DClassManager;
use app\decibel\database\statement\DJoin;
use app\decibel\database\statement\DLeftJoin;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\DChild;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DOneToManyRelationalField;
use app\decibel\model\search\DBaseModelSearch;

/**
 * Represents a field that can contain a link to child objects.
 *
 * @author        Timothy de Paris
 */
class DChildObjectsField extends DOneToManyRelationalField
{
    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $tableSuffix A suffix to append to the table name.
     *
     * @return    string
     */
    public function getFieldSql($tableSuffix = '')
    {
        return "`decibel_model_field_dchildobjectsfield_{$this->name}{$tableSuffix}`.`id`";
    }

    /**
     * Returns information about how this field can be joined to from the
     * table of the field's object.
     *
     * @param    string $tableSuffix      A suffix to append to the table name.
     * @param    DJoin  $joinFrom         A {@link app::decibel::database::statement::DJoin DJoin}
     *                                    object representing the    left side of this
     *                                    join. If not provided, the lowest level
     *                                    of the model hierarchy will be joined from.
     *
     * @return    DJoin
     */
    public function getJoin($tableSuffix = '', DJoin $joinFrom = null)
    {
        // Determine which table to join from based on the provided join level.
        $leftSide = $this->getJoinTable($joinFrom);
        $rightSide = "decibel_model_field_dchildobjectsfield_{$this->name}{$tableSuffix}";

        return new DLeftJoin(
            'decibel_model_dchild',
            "(`{$leftSide}`.`id`=`{$rightSide}`.`parent`
				AND `{$rightSide}`.`child_parentField`='{$this->name}')",
            $rightSide
        );
    }

    /**
     * Creates a join between the provided fields.
     *
     * @param    DField $to               The field to join to.
     * @param    string $fromAlias        The alias used for the join
     *                                    to the from field.
     * @param    string $aliasSuffix      The current alias suffix for this part
     *                                    of the search.
     *
     * @return    DJoin
     */
    public function getJoinTo(DField $to, $fromAlias, $aliasSuffix)
    {
        $toTable = 'decibel_model_dchild';
        $toAlias = $toTable . $aliasSuffix;

        return new DJoin(
            $toTable,
            "(`{$fromAlias}`.`id`=`{$toAlias}`.`parent`
				AND `{$toAlias}`.`child_parentField`='{$this->getName()}')",
            $toAlias
        );
    }

    /**
     * Returns SQL allowing selection of this field from the database,
     * returning the string value of the field where possible.
     *
     * @param    string $alias            If provided, this alias will be applied
     *                                    to the field in the returned SQL.
     * @param    string $tableSuffix      A suffix to append to the table name.
     * @param    DJoin  $joinFrom         A {@link app::decibel::database::statement::DJoin DJoin}
     *                                    object representing the    left side of this
     *                                    join. If not provided, the lowest level
     *                                    of the model hierarchy will be joined from.
     *
     * @return    array
     */
    public function getStringValueSql($alias = null, $tableSuffix = '',
                                      DJoin $joinFrom = null)
    {
        // Determine the name of the string value field.
        $linkTo = $this->linkTo;
        $stringValueFieldName = $linkTo::getStringValueFieldName();
        // @todo Can this be made more efficient, as per getSelectSql()?
        $selectSql = "(SELECT GROUP_CONCAT(`decibel_model_dmodel_{$this->name}`.`{$stringValueFieldName}` SEPARATOR ', ')
			FROM `decibel_model_dchild`
			JOIN `decibel_model_dmodel` AS `decibel_model_dmodel_{$this->name}`
				ON (`decibel_model_dmodel_{$this->name}`.`id`=`decibel_model_dchild`.`id`)
			WHERE `decibel_model_dchild`.`parent`=`{$this->table}`.`id`
				AND `decibel_model_dchild`.`child_parentField`='{$this->name}'";
        if ($alias) {
            $selectSql .= " AS `{$alias}`";
        }

        return array(
            'join' => null,
            'sql'  => $selectSql,
        );
    }

    /**
     * Compares to values for this field to determine if they are equal.
     *
     * @param    mixed $value1 The first value.
     * @param    mixed $value2 The second value.
     *
     * @return    bool    true if the values are equal, false otherwise.
     */
    public function compareValues($value1, $value2)
    {
        // If size is different, change has occured.
        if (count($value1) !== count($value2)) {
            return false;
        }
        // Otherwise, check if any child object has been changed,
        // or if the order has been changed (for ordered fields) or
        // any of the objects have been replaced.
        if ($this->orderValues($value1)
            || $this->orderValues($value2)
        ) {
            // A child has unsaved changes,
            // so these are not the same.
            return false;
        }
        // Ignore position for non-ordered fields.
        if (!$this->orderable) {
            $value1 = array_values($value1);
            $value2 = array_values($value2);
        }

        return (serialize($value1) === serialize($value2));
    }

    ///@cond INTERNAL
    /**
     * Orders values by their position so they can be compared with
     * another set of values.
     *
     * @param    array $values            Pointer to the values to order.
     * @param    bool  $unsavedChanges    Pointer which will be set
     *                                    to <code>true</code> if any child
     *                                    objects in the provided list of values
     *                                    has unsaved changes.
     *
     * @return    bool    <code>true</code> if any child objects in the provided
     *                    list of values has unsaved changes. If this is the case,
     *                    the values will not be ordered.
     */
    protected function orderValues(&$values)
    {
        $orderedValues = array();
        foreach ($values as $value) {
            // Normalise the value.
            if (is_array($value)) {
                $child = DModel::create($value['id']);
            } else {
                if (!is_object($value)) {
                    $child = DModel::create((int)$value);
                } else {
                    $child = $value;
                }
            }
            if ($child->hasUnsavedChanges()) {
                return true;
                // Store ordered position.
            } else {
                $position = $child->getFieldValue(DChild::FIELD_POSITION);
                $orderedValues[ $position ] = $child->getId();
            }
        }
        $values = $orderedValues;

        return false;
    }
    ///@endcond
    /**
     * Returns a search that can be used to locate the DChild objects
     * that belong to this field.
     *
     * @return    DBaseModelSearch
     */
    public function getSearch()
    {
        $linkTo = $this->linkTo;
        if (!$linkTo) {
            $linkTo = DChild::class;
        }

        return $linkTo::search()
                      ->filterByField(DChild::FIELD_PARENT_FIELD, $this->name)
                      ->sortByField(DChild::FIELD_POSITION);
    }

    /**
     * Sets whether data for this field is exportable.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $exportable Whether data for this field is exportable.
     *
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @return    void
     */
    public function setExportable($exportable)
    {
        throw new DReadOnlyParameterException('exportable', $this->name);
    }

    /*
     * Sets the type of model this field will link to.
     *
     * @todo	This should restrict the linkTo field to valid child objects,
     *			however for some reason this wasn't implemented. Implement it!
     *
     * @param	string	$linkTo	Qualified name of the model class
     *							or interface this field will link to.
     * @return	DRelationalField
     * @throws	DInvalidParameterValueException	If the parameter value is invalid.
     */
    public function setLinkTo($linkTo)
    {
        if (!DClassManager::isValidClassName($linkTo, DChild::class)) {
            throw new DInvalidParameterValueException(
                'linkTo',
                array(__CLASS__, __FUNCTION__),
                'valid qualified name of an object that extends app\\decibel\\model\\DChild'
            );
        }

        return parent::setLinkTo($linkTo);
    }
}
