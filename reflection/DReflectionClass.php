<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\reflection;

use app\decibel\model\field\DField;
use app\decibel\reflection\DReflectionProperty;
use app\decibel\regional\DLabel;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Provides advanced class reflection for Decibel.
 *
 * @author    Timothy de Paris
 */
class DReflectionClass extends ReflectionClass
{
    /**
     * A human-readable description of the class.
     *
     * @var        DLabel
     */
    protected $description;
    /**
     * A human-readable name of the class.
     *
     * @var        DLabel
     */
    protected $displayName;
    /**
     * Field definitions associated with the reflected class.
     *
     * @var        array
     */
    protected $fields;
    /*
     * Qualified name of the reflected class.
     *
     * @var		string
     */
    protected $qualifiedName;

    /**
     * Creates a reflection of the provided class.
     *
     * @note
     * Any class can be reflected using a {@link DReflectionClass} object,
     * however classes that implement {@link DReflectable} will have additional
     * functionality available.
     *
     * @param    string $qualifiedName Qualified name of the class to reflect.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $this->qualifiedName = $qualifiedName;
        // Store additional information for Decibel reflectable classes.
        if ($this->implementsInterface('app\\decibel\\reflection\\DReflectable')
            && !$this->isAbstract()
        ) {
            $this->displayName = $qualifiedName::getDisplayName();
            $this->description = $qualifiedName::getDescription();
        }
    }

    /**
     * Returns a list of default properties.
     *
     * @return    array
     */
    public function getDefaultProperties()
    {
        $properties = parent::getDefaultProperties();
        // Add defined fields.
        foreach ($this->fields as $field) {
            /* @var $field DField */
            $properties[ $field->name ] = $field->getDefaultValue();
        }

        return $properties;
    }

    /**
     * Returns the description of the reflected class.
     *
     * @return    DLabel    The description, or <code>null</code>
     *                    if the description is not known.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the display name of the reflected class.
     *
     * @return    DLabel    The display name, or <code>null</code> if the display
     *                    name is not known.
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Returns the fields defined by the class this object reflects.
     *
     * @return    array    List of {@link app::decibel::model::field::DField DField}
     *                    objects.
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns a list of interfaces implements by the reflected classes.
     *
     * @param    bool $includeInternal        Whether to include internal PHP
     *                                        interfaces in the result.
     *
     * @return    array    List of qualified interface names,
     *                    ordered alphabetically.
     */
    public function getInterfaceNames($includeInternal = true)
    {
        $interfaceNames = array();
        foreach ($this->getInterfaces() as $interface) {
            /* @var $interface ReflectionClass */
            if ($includeInternal
                || !$interface->isInternal()
            ) {
                $interfaceNames[] = $interface->name;
            }
        }
        sort($interfaceNames);

        return $interfaceNames;
    }

    /**
     * Returns a list of parent classes for the reflected classes.
     *
     * @param    bool $includeInternal        Whether to include internal PHP
     *                                        classes in the result.
     *
     * @return    array    List of qualified class names.
     */
    public function getParentNames($includeInternal = true)
    {
        $parentNames = array();
        if (!$this->isInterface()) {
            $parent = $this->getParentClass();
            while ($parent && ($includeInternal || !$parent->isInternal())) {
                $parentNames[] = $parent->name;
                $parent = $parent->getParentClass();
            }
        }

        return $parentNames;
    }

    /**
     * Retrieves reflected properties.
     *
     * @param    int $filter Optional filter.
     *
     * @return    array
     */
    public function getProperties($filter = null)
    {
        $properties = parent::getProperties($filter);
        foreach ($this->fields as $field) {
            /* @var $field DField */
            $properties[] = new DReflectionProperty($this, $field);
        }

        return $properties;
    }

    /**
     * Retrieves a reflection of the specified property.
     *
     * @param    string $name Name of the property.
     *
     * @return    ReflectionProperty
     * @throws    ReflectionException    If the property doesn't exist.
     */
    public function getProperty($name)
    {
        if (isset($this->fields[ $name ])) {
            $property = new DReflectionProperty($this, $this->fields[ $name ]);
        } else {
            $property = parent::getProperty($name);
        }

        return $property;
    }

    /**
     * Returns a list of trait classes for the reflected classes.
     *
     * @note
     * This method is recursive and returns all traits used by the class and it's hierarchy.
     *
     * @return    array    List of qualified class names.
     */
    public function getTraitNames()
    {
        $traitNames = parent::getTraitNames();
        $parent = $this->getParentClass();
        while ($parent && !$parent->isInternal()) {
            $traitNames = array_merge($traitNames, $parent->getTraitNames());
            $parent = $parent->getParentClass();
        }
        sort($traitNames);

        return $traitNames;
    }

    /**
     * Checks if a property is defined.
     *
     * @param    string $name Name of the property.
     *
     * @return    bool
     */
    public function hasProperty($name)
    {
        if (isset($this->fields[ $name ])) {
            $hasProperty = true;
        } else {
            $hasProperty = parent::hasProperty($name);
        }

        return $hasProperty;
    }

    /**
     * Returns the qualified name of the reflected class.
     *
     * @return    string
     */
    public function getQualifiedName()
    {
        return $this->name;
    }

    /**
     * Tests the implementation of this class.
     *
     * @note
     * This method can be overriden to implement custom tests for special
     * reflection classes.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult} objects.
     */
    protected function performImplementationTest()
    {
        return array();
    }

    /**
     * Tests the implementation of this class against best practice
     * for %Decibel Apps.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult} objects.
     */
    public function testImplementation()
    {
        if ($this->isAbstract()) {
            $result = array();
        } else {
            $result = $this->performImplementationTest();
        }

        return $result;
    }
}
