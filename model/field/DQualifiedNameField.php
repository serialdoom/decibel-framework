<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\application\DClassManager;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DFieldSearch;
use app\decibel\model\field\DStringField;
use app\decibel\utility\DString;

/**
 * Represents a field that can contain the qualified name of a %Decibel class.
 *
 * @author        Timothy de Paris
 */
class DQualifiedNameField extends DStringField
{
    /**
     * Option specifying the ancestors of classes that will be accepted
     * as values for this field.
     *
     * @var        array
     */
    protected $ancestor;

    ///@cond INTERNAL
    /**
     * Handles setting of field options.
     *
     * @param    string $name  The name of the option to set.
     * @param    mixed  $value The new value.
     *
     * @throws    DInvalidPropertyException        If the parameter does not exist.
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     * @return    void
     */
    public function __set($name, $value)
    {
        // Override required as the setter is pluralised.
        if ($name === 'ancestor') {
            $this->setAncestors((array)$value);
        } else {
            parent::__set($name, $value);
        }
    }
    ///@endcond
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
            $castValue = null;
        } else {
            if (is_string($value)
                && $this->isValidClassName($value)
            ) {
                $castValue = $value;
            } else {
                // If we haven't returned yet, it isn't valid!
                throw new DInvalidFieldValueException($this, $value);
            }
        }

        return $castValue;
    }

    /**
     * Returns a human-readable description of the internal data type
     * requirements of this field.
     *
     * This description is used by the {@link DInvalidFieldValueException}
     * class when thrown by the {@link DField::castValue()} method.
     *
     * @return    string
     */
    public function getInternalDataTypeDescription()
    {
        if ($this->ancestor) {
            $classNames = DString::implode(
                $this->ancestor,
                '</code>, <code>', '</code> or <code>'
            );
            $description = "Valid <code>{$classNames}</code> qualified class name";
        } else {
            $description = 'Valid qualified class name';
        }

        return $description;
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
        $options = new DFieldSearch($this);
        $widget = $options->getWidget();
        $widget->multiple = true;
        $widget->setNullOption('');

        return $options;
    }

    /**
     * Returns a list of possible values for this field.
     *
     * @return    array    List containing qualified names of valid classes
     *                    as keys and values.
     */
    public function getValues()
    {
        // Classes with a specific ancestor.
        if ($this->ancestor) {
            $classes = array();
            foreach ($this->ancestor as $ancestor) {
                $classes += DClassManager::getClasses($ancestor);
            }
            // All available classes.
        } else {
            $classes = DClassManager::getClasses();
        }
        // Ensure qualified names are both keys and values in the array.
        if ($classes) {
            $classes = array_combine($classes, $classes);
        }

        return $classes;
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

    /**
     * Determines whether the specified class name is valid for this field,
     * based on the assigned ancestors.
     *
     * @param    string $className Class name to validate.
     *
     * @return    bool
     */
    public function isValidClassName($className)
    {
        if ($this->ancestor) {
            $valid = false;
            foreach ($this->ancestor as $ancestor) {
                if (DClassManager::isValidClassName($className, $ancestor)) {
                    $valid = true;
                    break;
                }
            }
            // If no ancestors specified, it just
            // needs to be a valid class name.
        } else {
            $valid = class_exists($className);
        }

        return $valid;
    }

    /**
     * Sets the ancestors for which qualified names may be selected.
     *
     * @param    array $ancestors List of qualified class names.
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     */
    public function setAncestors(array $ancestors)
    {
        // Backwards compatibility.
        $ancestors = (array)$ancestors;
        foreach ($ancestors as $ancestor) {
            if (!is_string($ancestor)
                || (!class_exists($ancestor) && !interface_exists($ancestor))
            ) {
                throw new DInvalidParameterValueException(
                    'ancestor',
                    array(__CLASS__, __FUNCTION__),
                    'valid qualified name or array of valid qualified names'
                );
            }
        }
        $this->ancestor = $ancestors;
    }

    /**
     * Sets default options for this field.
     *
     * @return    void
     */
    protected function setDefaultOptions()
    {
        $this->exportable = false;
        $this->randomisable = false;
        $this->maxLength = 100;
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
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setExportable($exportable)
    {
        throw new DReadOnlyParameterException('exportable', $this->name);
    }

    /**
     * Sets the maximum number of characters allowed for strings assigned
     * as values of this field.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    int $maxLength       The maximum number of characters,
     *                                or <code>null</code> if no maximum length
     *                                applies.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setMaxLength($maxLength)
    {
        throw new DReadOnlyParameterException('maxLength', $this->name);
    }

    /**
     * Sets whether data for this field can be randomised for testing purposes.
     *
     * @warning
     * As this field is read-only for this field type,
     * a {@link app::decibel::debug::DReadOnlyParameterException DReadOnlyParameterException}
     * will always be thrown by this method.
     *
     * @param    bool $randomisable       Whether data for this field
     *                                    can be randomised.
     *
     * @return    void
     * @throws    DReadOnlyParameterException        If the parameter is read-only.
     */
    public function setRandomisable($randomisable)
    {
        throw new DReadOnlyParameterException('randomisable', $this->name);
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
