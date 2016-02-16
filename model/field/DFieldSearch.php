<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\model\field\DField;
use app\decibel\model\search\DBaseModelSearch;
use app\DecibelCMS\Model\Field\DFieldWidgetMapper;
use app\DecibelCMS\Widget\Widget;

/**
 * Provides information about how a search can be performed
 * on a particular field.
 *
 * @author        Timothy de Paris
 */
class DFieldSearch
{
    /**
     * Equal search operator.
     *
     * @var        string
     */
    const OPERATOR_EQUAL = '=';

    /**
     * Not equal search operator.
     *
     * @var        string
     */
    const OPERATOR_NOT_EQUAL = '!=';

    /**
     * Like search operator.
     *
     * @var        string
     */
    const OPERATOR_LIKE = ' LIKE ';

    /**
     * Not Like search operator.
     *
     * @var        string
     */
    const OPERATOR_NOT_LIKE = ' NOT LIKE ';

    /**
     * Greater than search operator.
     *
     * @var        string
     */
    const OPERATOR_GREATER_THAN = '>';

    /**
     * Greater than or equal to search operator.
     *
     * @var        string
     */
    const OPERATOR_GREATER_THAN_OR_EQUAL = '>=';

    /**
     * Less than search operator.
     *
     * @var        string
     */
    const OPERATOR_LESS_THAN = '<';

    /**
     * Less than or equal to search operator.
     *
     * @var        string
     */
    const OPERATOR_LESS_THAN_OR_EQUAL = '<=';

    /**
     * In search operator.
     *
     * @var        string
     */
    const OPERATOR_IN = ' IN ';

    /**
     * Not in search operator.
     *
     * @var        string
     */
    const OPERATOR_NOT_IN = ' NOT IN ';

    /**
     * Between search operator.
     *
     * @var        string
     */
    const OPERATOR_BETWEEN = ' BETWEEN ';

    /**
     * Null value search operator.
     *
     * @var        string
     */
    const OPERATOR_IS_NULL = ' IS NULL';

    /**
     * Not null value search operator.
     *
     * @var        string
     */
    const OPERATOR_NOT_NULL = ' IS NOT NULL ';

    /**
     * The field this object was created for.
     *
     * @var        DField
     */
    protected $field;

    /**
     * Name of the search field
     *
     * @var        string
     */
    protected $name;

    /**
     * Human readable name of the search field
     *
     * @var        string
     */
    protected $displayName;

    /**
     * List of available operators for searching on this field.
     *
     * This list does not limit the types of searches that can be
     * programmatically performed on the field using the {@link app::decibel::model::search::DModelSearch DModelSearch},
     * it only controls the operators available to users when searching for
     * an object through the administration panel.
     *
     * This should be an associative array of operator / display name pairs.
     * Operators must be one of the operator constants defined in this class.
     *
     * @var        array
     */
    protected $operators;

    /**
     * The field's widget.
     *
     * This will be used to allow selection of search values
     * in the administration panel.
     *
     * @var        Widget
     */
    protected $widget;

    /**
     * The currently selected operator for this field, if available.
     *
     * Must be one of the operator constants defined in this class.
     *
     * @var        string
     */
    protected $operator;

    /**
     * Creates a new {@link app::decibel::model::field::DFieldSearch DFieldSearch} object.
     *
     * @return    static
     */
    public function __construct(DField $field = null, Widget $widget = null)
    {
        if ($field) {
            $this->field = $field;
            $this->name = $field->name;
            $this->displayName = $field->displayName;
            if ($widget) {
                $this->widget = $widget;
            } else {
                $widgetMapper = DFieldWidgetMapper::decorate($field);
                $this->widget = clone $widgetMapper->getWidget(null);
            }
        }
    }

    /**
     * Apply the desired filter depending on the field type
     *
     * @param DBaseModelSearch $search
     * @param array            $criteria
     */
    public function applyCondition(DBaseModelSearch $search, $criteria)
    {
        set_default($criteria['operator'], null);
        // If operator is "IS NULL" or "IS NOT NULL",
        // default the value to null.
        if ($criteria['operator'] === self::OPERATOR_IS_NULL
            || $criteria['operator'] === self::OPERATOR_NOT_NULL
        ) {
            $criteria['value'] = null;
        }
        // Replace asterix characters with percent signs for LIKE search.
        $criteria['value'] = str_replace('*', '%', $criteria['value']);
        // Field search.
        if ($this->field) {
            $search->filterByField($this->name, $criteria['value'], $criteria['operator']);
            // Index search.
        } else {
            $search->filterByIndex($this->name, $criteria['value'], $criteria['operator']);
        }
    }

    /**
     * Returns the display name of this field search.
     *
     * @return    string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Returns the name of this field search.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the operator for this field search.
     *
     * @return    string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Returns a list of available operators for searching on this field.
     *
     * @return    array
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Returns the widget that will be used to select values
     * for this field search.
     *
     * @return    Widget
     */
    public function getWidget()
    {
        // Ensure widget has been assigned the correct name.
        $this->widget->setName("search_{$this->name}");

        return $this->widget;
    }

    /**
     * Sets the operator for this field search.
     *
     * @param    string $operator The operator.
     *
     * @return    void
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * Normalises and sets the selected value for this search.
     *
     * @param    mixed $value The value.
     *
     * @return    void
     */
    public function setValue($value)
    {
        if ($this->field) {
            // Handle serialisation of multiple value
            // selections for single value widgets.
            if (is_array($value)
                && $this->widget->multiple
                && $this->field->isNativeField()
            ) {
                foreach ($value as &$value) {
                    $value = $this->field->serialize($value);
                }
            } else {
                $value = $this->field->serialize($value);
            }
        }
        // Apply the normalised value to the widget.
        $this->widget->value = $value;
    }

    /**
     * Compares two {@link app::decibel::model::field::DFieldSearch DFieldSearch} objects for sorting.
     *
     * @param    DFieldSearch $a
     * @param    DFieldSearch $b
     *
     * @return    int
     */
    public static function sort($a, $b)
    {
        // Determine order based on display name.
        if (get_class($a->widget) === get_class($b->widget)) {
            $cmp = strcmp($a->displayName, $b->displayName);
            // Determine order based on widget type.
        } else {
            $orderA = $a->widget->getSearchWidgetPriority();
            $orderB = $b->widget->getSearchWidgetPriority();
            if ($orderA < $orderB) {
                $cmp = -1;
            } else {
                $cmp = 1;
            }
        }

        return $cmp;
    }
}
