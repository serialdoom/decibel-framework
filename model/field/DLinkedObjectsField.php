<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\application\DClassManager;
use app\decibel\database\statement\DJoin;
use app\decibel\database\statement\DLeftJoin;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\DModel;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DOneToManyRelationalField;
use app\DecibelCMS\Widget\LinkedObjectWidget;

/**
 * Represents a field that can contain a link to another object.
 *
 * @author        Timothy de Paris
 */
class DLinkedObjectsField extends DOneToManyRelationalField
{
    /**
     * Allows setting of additional options that will be passed through
     * to the {@link app::decibel::model::DModel::link() DModel::link()} method.
     *
     * @var        array
     */
    protected $additionalOptions = array();

    /**
     * Handles dynamic setting of widget parameters.
     *
     * @param    string $name  The name of the parameter to set.
     * @param    mixed  $value The new parameter value.
     *
     * @throws    DInvalidPropertyException        If the parameter does not exist.
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function __set($name, $value)
    {
        // Pass through to the parent and if rejected
        // add to additional options array.
        try {
            parent::__set($name, $value);
        } catch (DInvalidPropertyException $e) {
            $this->additionalOptions[ $name ] = $value;
        }
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
        if (count($value1) != count($value2)) {
            return false;
        }
        $value1 = $this->serialize($value1);
        $value2 = $this->serialize($value2);
        // Sort by id for non-ordered fields then compare.
        if (!$this->orderable) {
            sort($value1);
            sort($value2);
        }

        return (serialize($value1) === serialize($value2));
    }

    /**
     * Returns sql representing this field within the database.
     *
     * @param    string $tableSuffix A suffix to append to the table name.
     *
     * @return    string
     */
    public function getFieldSql($tableSuffix = '')
    {
        return "`decibel_model_field_dlinkedobjectsfield_{$this->name}{$tableSuffix}`.`to`";
    }

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        $linkTo = $this->linkTo;
        if (interface_exists($linkTo)) {
            $search = DModel::search()
                            ->filterByField(DModel::FIELD_QUALIFIED_NAME, DClassManager::getClasses($linkTo));
        } else {
            $search = $linkTo::link($this->additionalOptions);
        }
        $ids = $search->removeDefaultFilters()
                      ->ignore($this->ignore)
                      ->limitTo(100)
                      ->getIds();
        $minLinks = $this->minLinks
            ? $this->minLinks
            : 0;
        $maxLinks = ($this->maxLinks && $this->maxLinks < 10)
            ? $this->maxLinks
            : 10;
        // Randomise the array.
        shuffle($ids);
        // Determine a random number of links to return.
        $linkCount = rand($minLinks, $maxLinks);
        if ($linkCount > count($ids)) {
            $linkCount = count($ids);
        }
        $links = array();
        for ($i = 0; $i < $linkCount; ++$i) {
            $links[] = $ids[ $i ];
        }

        return $links;
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
			FROM `decibel_model_field_dlinkedobjectsfield`
			JOIN `decibel_model_dmodel` AS `decibel_model_dmodel_{$this->name}`
				ON (`decibel_model_dmodel_{$this->name}`.`id`=`decibel_model_field_dlinkedobjectsfield`.`to`)
			WHERE `decibel_model_field_dlinkedobjectsfield`.`from`=`{$this->table}`.`id`
				AND `decibel_model_field_dlinkedobjectsfield`.`field`='{$this->name}'";
        if ($alias) {
            $selectSql .= " AS `{$alias}`";
        }

        return array(
            'join' => null,
            'sql'  => $selectSql,
        );
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
        $rightSide = "decibel_model_field_dlinkedobjectsfield_{$this->name}{$tableSuffix}";

        return new DLeftJoin(
            'decibel_model_field_dlinkedobjectsfield',
            "(`{$leftSide}`.`id`=`{$rightSide}`.`from`
				AND `{$rightSide}`.`field`='{$this->name}')",
            $rightSide
        );
    }

    /**
     * Returns information about how the fields used by this index can be searched.
     *
     * @return    DFieldSearch    The object describing how search can be
     *                            performed, or <code>null</code> if search
     *                            is not allowed or not possible.
     */
    public function getSearchOptions()
    {
        $options = new DFieldSearch($this);
        $options->setFieldValue('displayName', $this->displayName);
        $options->setFieldValue('name', $this->name);
        $widget = new LinkedObjectWidget($this->name);
        $widget->setLinkTo($this->linkTo);
        $widget->multiple = true;
        $widget->setAdditionalOptions($this->additionalOptions);
        $options->setFieldValue('widget', $widget);

        return $options;
    }

    /**
     * Returns information about utilisation of other model instances
     * by this field for the provided model instance.
     *
     * @param    DModel $instance The model instance being indexed.
     *
     * @return    array    List of utilisation, with model instance IDs as keys
     *                    and relational integrity types as values.
     */
    public function getUtilisation(DModel $instance)
    {
        $utilisation = array();
        // Load the value.
        $value = $instance->getSerializedFieldValue($this->name);
        foreach ($value as $linkedId) {
            $utilisation[ $linkedId ] = $this->relationalIntegrity;
        }

        return $utilisation;
    }

    /**
     * Sets an additional option that will be passed within
     * the <code>$options</code> parameter to the <code>link()</code>
     * method for the linked object.
     *
     * @param    string $name  Name of the option
     * @param    mixed  $value Option value.
     *
     * @return    static
     */
    public function setAdditionalOption($name, $value)
    {
        $this->additionalOptions[ $name ] = $value;

        return $this;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        parent::setDefaultOptions();
        $this->orderable = false;
    }
}
