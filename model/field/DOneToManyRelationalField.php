<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DDebug;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DRelationalField;
use app\decibel\utility\DPersistable;

/**
 * Represents a field that can contain a relationship between this object
 * and a set of other objects.
 *
 * @author        Timothy de Paris
 */
abstract class DOneToManyRelationalField extends DRelationalField
{
    /**
     * Whether related objects are orderable within the field.
     *
     * @var        bool
     */
    protected $orderable = true;

    /**
     * The minimum number of links required for this field.
     *
     * @var        int
     */
    protected $minLinks = 0;

    /**
     * The maximum number of links available for this field.
     *
     * If null, there will be no maximum.
     *
     * @var        int
     */
    protected $maxLinks = null;

    /**
     * Attempts to convert the provided data into a value that
     * can be assigned to a field of this type.
     *
     * @param    mixed $value The value to cast.
     *
     * @return    mixed    The cast value
     * @throws    DInvalidFieldValueException    If the provided value cannot
     *                                        be cast for this field.
     */
    public function castValue($value)
    {
        if ($this->isNull($value)) {
            return array();
        }
        if (!is_array($value)) {
            $value = (array)$value;
        }
        foreach ($value as &$subValue) {
            // Convert persistable objects to their ID.
            if (is_object($subValue)
                && $subValue instanceof DPersistable
            ) {
                $id = $subValue->getId();
                if ($id !== 0) {
                    $subValue = $id;
                }
                // Ensure numeric values are integers.
            } else {
                if (is_numeric($subValue)) {
                    $subValue = (int)$subValue;
                    // Anything else is invalid.
                } else {
                    throw new DInvalidFieldValueException($this, $value);
                }
            }
        }

        return $value;
    }

    ///@cond INTERNAL
    /**
     * Provides debugging information about a value for this field.
     *
     * @param    mixed $data          Data to convert.
     * @param    bool  $showType      Pointer in which a decision about whether
     *                                the datatype of the debug message should
     *                                be shown will be returned.
     *
     * @return    string    The debugged data as a string.
     */
    public function debugValue($data, &$showType)
    {
        $values = array();
        foreach ($data as $value) {
            if (is_numeric($value)) {
                $value = $this->getInstanceFromId((int)$value);
            }
            if ($value) {
                $qualifiedName = get_class($value);
                $values[ '*' . $value->getId() ] = "object({$qualifiedName}|{$value->getId()}) [{$value}]";
                $value->free();
            } else {
                $values[ '*' . (int)$value ] = "[-- Deleted --]";
            }
        }
        $showType = false;
        $debug = new DDebug($values);

        return $debug->getMessage();
    }
    ///@endcond
    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DField::DATA_TYPE_SPECIAL;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        return 'array';
    }

    /**
     * Returns the maximum number of objects this field can link to.
     *
     * @return    int
     */
    public function getMaxLinks()
    {
        return $this->maxLinks;
    }

    /**
     * Returns the minimum number of objects this field may link to.
     *
     * @return    int
     */
    public function getMinLinks()
    {
        return $this->minLinks;
    }

    /**
     * Returns the default value for this type of field.
     *
     * This value will be used if no default value is supplied for the field.
     *
     * @return    bool
     */
    public function getStandardDefaultValue()
    {
        return array();
    }

    /**
     * Determines if this field can be used for ordering.
     *
     * @return    bool
     */
    public function isOrderable()
    {
        return false;
    }

    ///@cond INTERNAL
    /**
     * Process the value for this field within a row of model search results.
     *
     * @param    array  $row   The results to process.
     * @param    string $alias Alias of the field to process.
     *
     * @return    void
     */
    public function processRow(array &$row, $alias = null)
    {
        if ($alias === null) {
            $alias = $this->name;
        }
        if (isset($row[ $alias ])) {
            $row[ $alias ] = explode(',', $row[ $alias ]);
        }
    }
    ///@endcond
    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->relationalIntegrity = DRelationalField::RELATIONAL_INTEGRITY_NONE;
    }

    /**
     * Sets the maximum number of links available for this field.
     *
     * @param    int $maxLinks        The maximum number of links,
     *                                or <code>null</code> if there is no limit.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setMaxLinks($maxLinks)
    {
        $this->setInteger('maxLinks', $maxLinks, true);

        return $this;
    }

    /**
     * Sets the minimum number of links required for this field.
     *
     * @param    int $minLinks The minimum number of links.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setMinLinks($minLinks)
    {
        $this->setInteger('minLinks', $minLinks);

        return $this;
    }

    /**
     * Sets whether objects are orderable within the field.
     *
     * @param    bool $orderable Whether objects are orderable within the field.
     *
     * @return    static
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setOrderable($orderable)
    {
        $this->setBoolean('orderable', $orderable);

        return $this;
    }

    /**
     * Converts a data value for this field to its string equivalent.
     *
     * @param    mixed $data The data to convert.
     *
     * @return    string    The string value of the data.
     */
    public function toString($data)
    {
        return (string)$data;
    }
}
