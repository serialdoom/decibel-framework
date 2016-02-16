<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\utility;

use app\decibel\application\DClassManager;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\regional\DLabel;
use app\decibel\utility\DString;

/**
 *
 * @author        Timothy de Paris
 */
trait DBasicDefinable
{
    ///@cond INTERNAL
    /**
     * Handles retrieval of parameters.
     *
     * @note
     * This method provides backwards compatibility allowing properties
     * to be accessed as magic properties.
     *
     * @param    string $name The name of the parameter to retrieve.
     *
     * @return    mixed
     * @throws    DInvalidPropertyException    If the parameter does not exist.
     * @deprecated    In favour of the relevant getter method.
     */
    public function __get($name)
    {
        // Look for a getter method.
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            $value = $this->$getter();
            // Otherwise access the property directly.
        } else {
            if (property_exists($this, $name)) {
                $value = $this->$name;
                // Or throw an exception if it just doesn't exist.
            } else {
                throw new DInvalidPropertyException($name, get_class($this));
            }
        }

        return $value;
    }
    ///@endcond
    ///@cond INTERNAL
    /**
     * Handles setting of field options.
     *
     * @note
     * This method provides backwards compatibility allowing properties
     * to be accessed as magic properties.
     *
     * @param    string $name  The name of the option to set.
     * @param    mixed  $value The new value.
     *
     * @return    void
     * @throws    DInvalidPropertyException        If the parameter does not exist.
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @deprecated    In favour of the relevant setter method.
     */
    public function __set($name, $value)
    {
        // Look for a setter method.
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
            // Otherwise access the property directly.
        } else {
            if (property_exists($this, $name)) {
                $this->$name = $value;
                // Or throw an exception if it just doesn't exist.
            } else {
                throw new DInvalidPropertyException($name, get_class($this));
            }
        }
    }
    ///@endcond
    /**
     * Handles dynamic setting of array parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    protected function setArray($name, $value,
                                $allowNull = false, $expected = null)
    {
        if (is_array($value)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, 'array', $allowNull)
            );
        }
    }

    /**
     * Handles dynamic setting of integer parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setBoolean($name, $value,
                                  $allowNull = false, $expected = null)
    {
        if (is_bool($value)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, 'boolean', $allowNull)
            );
        }
    }

    /**
     * Handles dynamic setting of enumerated parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    array  $values    Allowed values.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setEnum($name, $value, array $values,
                               $allowNull = false, $expected = null)
    {
        if (array_key_exists($value, $values)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            $type = 'One of ' . DString::implode($values, ', ', ' or ');
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, $type, $allowNull)
            );
        }
    }

    /**
     * Handles dynamic setting of integer parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setInteger($name, $value,
                                  $allowNull = false, $expected = null)
    {
        if (is_int($value)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, 'integer', $allowNull)
            );
        }
    }

    /**
     * Handles dynamic setting of label parameters.
     *
     * @param    string $name        The name of the parameter to set.
     * @param    mixed  $value       The new parameter value.
     * @param    bool   $allowString Whether a string value will also be accepted.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setLabel($name, $value, $allowString = true)
    {
        if (is_object($value)
            && $value instanceof DLabel
        ) {
            $this->$name = $value;
            // Pass through to the _setString() method if allowed.
        } else {
            if ($allowString) {
                try {
                    $this->setString($name, $value, true);
                } catch (DInvalidParameterValueException $exception) {
                    throw new DInvalidParameterValueException(
                        $name,
                        get_class($this),
                        'string, <code>app\decibel\regional\DLabel</code> instance or <code>null</code>'
                    );
                }
            } else {
                throw new DInvalidParameterValueException(
                    $name,
                    get_class($this),
                    'string, <code>app\decibel\regional\DLabel</code> instance or <code>null</code>'
                );
            }
        }
    }

    /**
     * Handles dynamic setting of enumerated parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    string $ancestor  The required ancestor of the value.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setQualifiedName($name, $value, $ancestor = null,
                                        $allowNull = false, $expected = null)
    {
        if (DClassManager::isValidClassName($value, $ancestor)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            $type = "Qualified name of a class that extends <code>{$ancestor}</code>";
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, $type, $allowNull)
            );
        }
    }

    /**
     * Handles dynamic setting of string parameters.
     *
     * @param    string $name      The name of the parameter to set.
     * @param    mixed  $value     The new parameter value.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     * @param    string $expected  Message explaining the expected value.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    void
     */
    protected function setString($name, $value,
                                 $allowNull = false, $expected = null)
    {
        if (is_string($value)
            || ($allowNull && $value === null)
        ) {
            $this->$name = $value;
        } else {
            throw new DInvalidParameterValueException(
                $name,
                get_class($this),
                $this->getExpectedMessage($expected, 'string', $allowNull)
            );
        }
    }

    /**
     * Builds a message describing the expected value for any
     * thrown {@link DInvalidParameterValueException}.
     *
     * @param    string $expected  Provided message, if any.
     * @param    string $type      Expected type description.
     * @param    bool   $allowNull Whether <code>null</code> is a valid value.
     *
     * @return    string
     */
    protected function getExpectedMessage($expected, $type, $allowNull)
    {
        // Need to build the message.
        if ($expected === null) {
            if ($allowNull) {
                $expected = "{$type} or <code>null</code>";
            } else {
                $expected = $type;
            }
        }

        return $expected;
    }
}
