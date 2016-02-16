<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\database\schema\DColumnDefinition;
use app\decibel\model\DBaseModel;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\DModel;
use app\decibel\model\field\DField;
use app\decibel\model\field\DRelationalField;
use Exception;

/**
 * Represents a field that can contain a relationship between this object
 * and one other object.
 *
 * @author        Timothy de Paris
 */
abstract class DOneToOneRelationalField extends DRelationalField
{
    /**
     * Used for compatibility reasons (the database field is actually unsigned int)
     *
     * @var        boolean
     */
    protected $unsigned = true;

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
            return null;
        }
        $qualifiedName = $this->linkTo;
        if ($qualifiedName === null) {
            $qualifiedName = DBaseModel::class;
        }
        if (is_object($value)
            && $value instanceof $qualifiedName
        ) {
            return $value->getId();
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        throw new DInvalidFieldValueException($this, $value);
    }

    /**
     * Returns the data type used by this field in the database.
     *
     * @return    string
     */
    public function getDataType()
    {
        return DField::DATA_TYPE_BIGINT;
    }

    /**
     * Returns a DColumnDefinition object representing the database column
     * needed for storing this field or null.
     *
     * @return    DColumnDefinition
     */
    public function getDefinition()
    {
        $definition = parent::getDefinition()
                            ->setUnsigned(true);
        if (!$this->required
            && $this->nullOption === null
        ) {
            $definition->setNull(true);
        }

        return $definition;
    }

    /**
     * Returns the data type used by this field with PHP.
     *
     * @return    string
     */
    public function getInternalDataType()
    {
        if ($this->linkTo) {
            return $this->linkTo;
        }

        return 'object';
    }

    /**
     * Returns a regular expression that can be used to match the string
     * version of data for this field.
     *
     * @return    string    A regular expression, or <code>null</code> if it is
     *                    not possible to match via a regular expression.
     */
    public function getRegex()
    {
        return '[0-9]+';
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
        return null;
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
        $linkedId = $instance->getSerializedFieldValue($this->name);
        // Non-strict check as this could still be 0 or null.
        if ($linkedId) {
            $utilisation[ $linkedId ] = $this->relationalIntegrity;
        }

        return $utilisation;
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
        if ($data === null || $data === 0 || $data === '0') {
            if ($this->nullOption !== null) {
                $stringValue = $this->nullOption;
            } else {
                $stringValue = '-- None --';
            }
        } else {
            if (is_numeric($data)) {
                try {
                    $object = $this->getInstanceFromId((int)$data);
                    if ($object === null) {
                        $stringValue = '-- Deleted --';
                    } else {
                        // Get the object's string value.
                        $stringValue = (string)$object;
                        // Free the model to save memory
                        // (in case we are working on a big set of data).
                        $object->free();
                    }
                } catch (Exception $e) {
                    $stringValue = '-- Deleted --';
                }
                // Data is a model instance (or something else?).
            } else {
                $stringValue = (string)$data;
            }
        }

        return $stringValue;
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
        if ($data instanceof DModel) {
            $qualifiedName = get_class($data);
            $message = "{$qualifiedName}::{$data->getId()} [{$data}]";
        } else {
            if ($data == 0
                && $this->nullOption !== null
            ) {
                $message = "NULL [{$this->nullOption}]";
            } else {
                if ($data == 0) {
                    $message = '[-- None --]';
                } else {
                    try {
                        $object = $this->getInstanceFromId((int)$data);
                        if ($object) {
                            $qualifiedName = get_class($object);
                            $message = "object({$qualifiedName}|{$object->getId()}) [{$object}]";
                        } else {
                            $message = '[-- None --]';
                        }
                    } catch (Exception $e) {
                        $message = '[-- Deleted --]';
                    }
                }
            }
        }
        $showType = false;

        return $message;
    }
    ///@endcond
}
