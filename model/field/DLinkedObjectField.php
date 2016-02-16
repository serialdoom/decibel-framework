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
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\DLightModel;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DOneToOneRelationalField;

/**
 * Represents a field that can contain a link to another object.
 *
 * @author        Timothy de Paris
 */
class DLinkedObjectField extends DOneToOneRelationalField
{
    /**
     * Allows setting of additional options that will be passed through
     * to the {@link app::decibel::model::DModel::link() DModel::link()} method.
     *
     * @var        array
     */
    protected $additionalOptions = array();

    /**
     * Image Filter Width
     *
     * @var        int
     */
    protected $imageResizeWidth = 0;

    /**
     * Image Filter Height
     *
     * @var        int
     */
    protected $imageResizeHeight = 0;

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
        // Check that supplied option is valid.
        if ($name === 'imageResizeWidth'
            || $name === 'imageResizeHeight'
        ) {
            $this->setInteger($name, $value);
        } else {
            // Pass through to the parent and if rejected
            // add to additional options array.
            try {
                parent::__set($name, $value);
            } catch (DInvalidPropertyException $e) {
                $this->setAdditionalOption($name, $value);
            }
        }
    }

    /**
     * Returns sql representing this field whch can be placed in ORDER BY clause.
     *
     * @return    array
     */
    public function getSortOptions()
    {
        $linkTo = $this->linkTo;
        // Generate a custom alias for this join.
        $alias = "{$this->name}_stringvalue";
        // Determine the name of the string value field.
        // If this is linked to an interface, sort cannot occur.
        if (!method_exists($linkTo, 'getStringValueFieldName')) {
            return null;
        }
        $stringValueFieldName = $linkTo::getStringValueFieldName();
        $selectSql = "`{$alias}`.`{$stringValueFieldName}`";
        // Join to the DModel table for the string value.
        $joins = array();
        $joins[] = new DLeftJoin(
            "`decibel_model_dmodel`",
            "{$this->getFieldSql()} = `{$alias}`.`id`",
            $alias
        );
        // Ensure a join exists to the table in which this field is stored.
        if ($this->ownerTable !== $this->addedByTable) {
            $joins[] = $this->getJoin();
        }

        return array(
            'join' => $joins,
            'sql'  => $selectSql,
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
        // Generate a custom alias for this join.
        $customAlias = "{$this->name}_stringvalue";
        $joins = array();
        // Determine the name of the string value field.
        $linkTo = $this->linkTo;
        // Determine the name of the string value field.
        // If this is linked to an interface, sort cannot occur.
        if (method_exists($linkTo, 'getStringValueFieldName')) {
            $stringValueFieldName = $linkTo::getStringValueFieldName();
            // Fall back for when linkTo is an interface.
            // @todo Find a better mechanism for this.
        } else {
            $stringValueFieldName = 'stringValue';
        }
        // For light models, join directly to the table, as there is no hierarchy.
        if (DClassManager::isValidClassName($linkTo, DLightModel::class)) {
            // Join to the linked object.
            $linkTableName = DDatabaseMapper::getTableNameFor($linkTo);
            $joins[] = new DLeftJoin(
                $linkTableName,
                "{$this->getFieldSql($tableSuffix)}=`{$customAlias}`.`id`",
                $customAlias
            );
            // For DModels, always join to the DModel table, even if this links to a multi-
            // lingual model, as a backup. In some cases the higher level
            // or abstract model actually linked to may not be multi-lingual,
            // while an ancestor may be multi-lingual.
        } else {
            $joins[] = new DLeftJoin(
                "`decibel_model_dmodel`",
                "{$this->getFieldSql($tableSuffix)}=`{$customAlias}`.`id`",
                $customAlias
            );
        }
        // Select string value from DModel table.
        $selectSql = "`{$customAlias}`.`{$stringValueFieldName}`";
        // Ensure a join exists to the table in which this field is stored.
        if ($this->ownerTable !== $this->addedByTable) {
            $joins[] = $this->getJoin();
        }
        if ($alias) {
            $selectSql .= " AS `{$alias}`";
        }

        return array(
            'join' => $joins,
            'sql'  => $selectSql,
        );
    }

    /**
     * Returns a random value suitable for assignment as the value of this field.
     *
     * @return    mixed
     */
    public function getRandomValue()
    {
        $linkTo = $this->linkTo;
        $search = $linkTo::link($this->additionalOptions)
                         ->removeDefaultFilters()
                         ->ignore($this->ignore)
                         ->limitTo(100)
                         ->getIds();
        if (!empty($search)) {
            return $search[ rand(0, count($search) - 1) ];
        }

        return null;
    }

    /**
     * Returns information about how the fields used by this index can be searched.
     *
     * @return    DFieldSearch    The object describing how search can be
     *                                performed, or null if search is not allowed
     *                                or possible.
     */
    public function getSearchOptions()
    {
        // Create search options descriptor.
        $options = new DFieldSearch($this);
        $widget = $options->getWidget();
        $widget->multiple = true;
        $widget->setNullOption('&nbsp;');

        return $options;
    }

    /**
     * Determines if this field can be used for ordering.
     *
     * @return    bool
     */
    public function isOrderable()
    {
        $linkTo = $this->linkTo;

        return method_exists($linkTo, 'getStringValueFieldName');
    }

    /**
     * Sets an additional option that will be included within
     * the <code>$options</code> array passed to the <code>link()</code>
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
}
