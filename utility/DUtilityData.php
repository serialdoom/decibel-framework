<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DDebuggable;
use app\decibel\debug\DException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DUtilityData;
use Iterator;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Base class for all utility data objects.
 *
 * @author        Timothy de Paris
 */
abstract class DUtilityData implements DDispatchable, DDebuggable,
                                       DDefinable, Iterator, JsonSerializable
{
    use DEventDispatcher;
    use DDefinableObject;
    use DDefinableCache;

    /**
     * The internal iterator position.
     *
     * @var        int
     */
    private $position;

    /**
     * List of available field names, used when iterating.
     *
     * @var        array
     */
    private $fieldNames;

    /**
     * Create an instance of the utility data object.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->loadDefinitions();
    }

    ///@cond INTERNAL
    /**
     * Handles retrieval of defined field values.
     *
     * @param    string $name The name of the field to retrieve.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @deprecated
     */
    public function __get($name)
    {
        $this->notifyDeprecatedPropertyAccess($name, "getFieldValue()");

        return $this->getFieldValue($name);
    }
    ///@endcond

    /**
     * Returns an array of cacheable fields.
     *
     * @return    array
     */
    public function __sleep()
    {
        if (count($this->fields) > 0) {
            $fields = array('fieldValues');
            // Handle old style properties.
        } else {
            $fields = $this->getFieldNames();
        }

        return $fields;
    }

    /**
     * Restores the object following unserialization.
     *
     * @return    array
     */
    public function __wakeup()
    {
        $this->loadDefinitions();
    }

    /**
     * Defines fields available for this object.
     *
     * This function should call the {@link DUtilityData::addField()} function.
     *
     * @return    void
     */
    abstract protected function define();

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return null;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array();
    }

    /**
     * Returns a list containing the names of all fields registered
     * for this utility data object.
     *
     * @note
     * This method is used by the {@link DUtilityData::__sleep},
     * {@link DUtilityData::generateDebug} and {@link DUtilityData::jsonPrepare}
     * methods to provide backwards compatibility for non-defined utility
     * data objects.
     *
     * @param    bool $protectedProperties        Whether protected properties
     *                                            will be included.
     *
     * @return    array
     */
    public function getFieldNames($protectedProperties = true)
    {
        // New style defined fields.
        if (count($this->fields) > 0) {
            return array_keys($this->fields);
        }
        // Handle old style properties.
        $fieldNames = array();
        $reflection = new ReflectionClass($this);
        // Determine filter for object properties.
        $filter = ReflectionProperty::IS_PUBLIC;
        if ($protectedProperties) {
            $filter += ReflectionProperty::IS_PROTECTED;
        }
        // Create each of the properties in the stdClass object.
        foreach ($reflection->getProperties($filter) as $property) {
            /* @var $property ReflectionProperty */
            // Ignore fields defined in this class, and static variables.
            if ($property->getDeclaringClass()->name !== self::class
                && !$property->isStatic()
            ) {
                $fieldNames[] = $property->getName();
            }
        }

        return $fieldNames;
    }

    /**
     * Retrieves the value for a specified field.
     *
     * @todo    Remove support for non-defined fields.
     *
     * @param    string $fieldName Name of the field to set the value for.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     */
    public function getFieldValue($fieldName)
    {
        // Handle non-defined fields.
        if (property_exists($this, $fieldName)
            && !in_array($fieldName, array('fieldValues', 'fields', 'position', 'fieldNames'))
        ) {
            $value = $this->$fieldName;
        } else {
            $field = $this->getField($fieldName);
            // Check if field needs to be unserialized.
            if (!array_key_exists($fieldName, $this->fieldPointers)) {
                $this->setFieldPointer($field);
            }
            // Return field value.
            $value = $this->fieldPointers[ $fieldName ];
        }

        return $value;
    }

    /**
     * Returns a copy of this object ready for encoding into json format.
     *
     * @return    stdClass
     */
    public function jsonSerialize()
    {
        $jsonObject = new stdClass();
        $fieldNames = $this->getFieldNames();
        foreach ($fieldNames as $fieldName) {
            $jsonObject->$fieldName = $this->getFieldValue($fieldName);
        }
        // Include a string representation of the object, if available.
        if (method_exists($this, '__toString')) {
            $jsonObject->__toString = $this->__toString();
        }
        // Add qualified name
        $jsonObject->_qualifiedName = get_class($this);

        return $jsonObject;
    }

    /**
     * Creates a {@link DUtilityData} object from the provided array of fields.
     *
     * @param    array $values            List of values from which
     *                                    to construct the object.
     * @param    bool  $ignoreErrors      If set to <code>true</code>, errors
     *                                    with the provided data will be ignored
     *                                    and therefore no values set for
     *                                    erroneous data and no exceptions thrown.
     *
     * @return    DUtilityData
     * @throws    DInvalidPropertyException    If a provided value is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If a provided value cannot be changed.
     * @throws    DInvalidFieldValueException    If a provided value is not valid
     *                                        for the field.
     */
    public static function fromArray(array $values, $ignoreErrors = false)
    {
        unset($values['_qualifiedName']);
        unset($values['__toString']);
        $object = new static();
        foreach ($values as $key => $value) {
            try {
                $object->setFieldValue($key, $value);
                // Catch any exceptions and decide whether to throw
                // them to the caller.
            } catch (DException $exception) {
                if (!$ignoreErrors) {
                    throw $exception;
                }
            }
        }

        return $object;
    }

    /**
     * Sets the value for a specified field.
     *
     * @todo    Remove support for non-defined fields.
     *
     * @param    string $fieldName Name of the field to set the value for.
     * @param    mixed  $value     The value to set.
     *
     * @return    void
     * @throws    DInvalidPropertyException    If the provided name is not
     *                                        that of a defined field.
     * @throws    DReadOnlyParameterException    If the value for this cannot
     *                                        be changed.
     * @throws    DInvalidFieldValueException    If the provided value is not valid
     *                                        for the field.
     * @todo    Remove this function once support for object properties removed.
     *            This can't override as the "parent" is defined in a trait (DDefinableObject)
     */
    public function setFieldValue($fieldName, $value)
    {
        // Handle non-defined fields.
        if (property_exists($this, $fieldName)) {
            $this->$fieldName = $value;
        } else {
            // Load the field to validate that it exists.
            $field = $this->getField($fieldName);
            // Cast the value to ensure it is compatible,
            // this will also serialize it.
            $castValue = $field->castValue($value);
            // Store the original value of the field.
            $this->storeOriginalValue($field, $castValue);
            // Store the serialized field value.
            $this->fieldValues[ $fieldName ] = $castValue;
            unset($this->fieldPointers[ $fieldName ]);
        }
    }

    /**
     * Returns an array containing the properties of this object.
     *
     * @param    bool $protectedProperties        Whether protected properties
     *                                            will be included.
     *
     * @return    array
     */
    public function toArray($protectedProperties = true)
    {
        return get_object_vars(
            $this->jsonPrepare($protectedProperties)
        );
    }

    /**
     * Returns the current field value when iterating.
     *
     * @return    mixed
     */
    public function current()
    {
        return $this->getFieldValue(
            $this->fieldNames[ $this->position ]
        );
    }

    /**
     * Returns the current field name when iterating.
     *
     * @return    string
     */
    public function key()
    {
        return $this->fieldNames[ $this->position ];
    }

    /**
     * Increments the internal iterator pointer.
     *
     * @return    void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Rewinds the internal iterator pointer.
     *
     * @return    void
     */
    public function rewind()
    {
        if ($this->fieldNames === null) {
            $this->fieldNames = array_keys($this->fields);
        }
        $this->position = 0;
    }

    /**
     * Determines if the current iterator position is valid.
     *
     * @return    bool
     */
    public function valid()
    {
        return isset($this->fieldNames[ $this->position ]);
    }
}
