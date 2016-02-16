<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\configuration\DApplicationMode;
use app\decibel\event\debug\DInvalidObserverException;
use app\decibel\model\database\DDatabaseMapper;
use app\decibel\model\debug\DInvalidDefinitionHierarchyException;
use ReflectionClass;

/**
 * Defines the base definition class for models.
 *
 * @author        Timothy de Paris
 */
abstract class DBaseModel_Definition extends DDefinition
{
    /**
     * Array containing functions to be called for handling internal events.
     *
     * @var        array
     */
    protected $eventHandlers;
    /**
     * Parameter definitions for this remote procedure.
     *
     * @var        array
     */
    public $fields;
    /**
     * Contains qualified names of the abstract and non-abstract objects
     * this object inherits from.
     *
     * This includes the qualified name of this object.
     *
     * @var        array
     */
    protected $inheritanceHierarchy;

    /**
     * Constructs an instance of this class
     *
     * @param    string $qualifiedName    Qualified name of the model this
     *                                    class defines (this may be itself).
     *
     * @return    DBaseModel_Definition
     */
    protected function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $this->tableName = DDatabaseMapper::getTableNameFor($qualifiedName);
        $this->displayName = $qualifiedName::getDisplayName();
        // Create a reflection object to determine required information.
        $reflection = new ReflectionClass($qualifiedName);
        $this->inheritanceHierarchy = $this->generateInheritanceHierarchy($reflection);
        // Initialise event handler array.
        $this->eventHandlers = array(
            DBaseModel::ON_BEFORE_LOAD       => array(),
            DBaseModel::ON_BEFORE_DELETE     => array(),
            DBaseModel::ON_BEFORE_FIRST_SAVE => array(),
            DBaseModel::ON_BEFORE_SAVE       => array(),
            DBaseModel::ON_DELETE            => array(),
            DBaseModel::ON_FIRST_SAVE        => array(),
            DBaseModel::ON_SUBSEQUENT_SAVE   => array(),
            DBaseModel::ON_LOAD              => array(),
            DBaseModel::ON_SAVE              => array(),
        );
    }

    /**
     * Returns an array of cacheable fields.
     *
     * @return    array
     */
    public function __sleep()
    {
        $fields = parent::__sleep();
        $fields[] = 'eventHandlers';
        $fields[] = 'inheritanceHierarchy';

        return $fields;
    }

    /**
     * Generates the inheritance hierarchy information for this model definition,
     * stored in the {@link DBaseModel_Definition::$inheritanceHierarchy} property.
     *
     * @param    ReflectionClass $reflection Reflection of the model class.
     *
     * @return    array
     * @throws    DInvalidDefinitionHierarchyException    In debug mode, if the
     *                                                    hierarchy of the definition
     *                                                    does not match the model.
     */
    protected function generateInheritanceHierarchy(ReflectionClass $reflection)
    {
        $inheritanceHierarchy = array();
        // Generate model hierarchy.
        while ($reflection->name !== DBaseModel::class) {
            $hierarchyQualifiedName = $reflection->getName();
            $inheritanceHierarchy[] = $hierarchyQualifiedName;
            $reflection = $reflection->getParentClass();
        }
        // Check that the inheritance hierarchy of the definition
        // matches that of the object.
        if (DApplicationMode::isDebugMode()) {
            $this->testInheritanceHierarchy($inheritanceHierarchy);
        }

        return $inheritanceHierarchy;
    }

    /**
     * Returns registered handlers for the specified event.
     *
     * @param    string $event
     *
     * @return    array
     */
    public function getEventHandlers($event)
    {
        if (isset($this->eventHandlers[ $event ])) {
            $eventHandlers = $this->eventHandlers[ $event ];
        } else {
            $eventHandlers = array();
        }

        return $eventHandlers;
    }

    /**
     * Returns the fields in this definition.
     *
     * @return    array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the inheritance hierarchy for this model definition.
     *
     * @return    array
     */
    public function getInheritanceHierarchy()
    {
        return $this->inheritanceHierarchy;
    }

    /**
     * Registers an event handler for this object definiton.
     *
     * @param    string $event    The event to be handled.
     * @param    string $function The function to be called when the event is triggered.
     *
     * @throws    DInvalidObserverException    If the supplied function is not available within this class.
     * @return    void
     */
    public function setEventHandler($event, $function)
    {
        // Check that the function is valid if running in debug mode.
        if (!method_exists($this->qualifiedName, $function)) {
            throw new DInvalidObserverException(array($this->qualifiedName, $function));
            // Add the event handler if it hasn't already been added.
        } else {
            if (!in_array($function, $this->eventHandlers[ $event ])) {
                $this->eventHandlers[ $event ][] = $function;
            }
        }
    }

    /**
     * Tests that the hierarchy of this definition matches that of the model it defines.
     *
     * @param    array $inheritanceHierarchy
     *
     * @throws    DInvalidDefinitionHierarchyException
     * @return    void
     */
    protected function testInheritanceHierarchy(array $inheritanceHierarchy)
    {
        $definitionReflection = new ReflectionClass($this);
        foreach ($inheritanceHierarchy as $hierarchyQualifiedName) {
            if ($definitionReflection->getName() !== "{$hierarchyQualifiedName}_Definition") {
                throw new DInvalidDefinitionHierarchyException(
                    get_class($this),
                    $this->qualifiedName
                );
            }
            $definitionReflection = $definitionReflection->getParentClass();
        }
    }
}
