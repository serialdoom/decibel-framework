<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\reflection;

use app\decibel\debug\DDebuggable;
use app\decibel\debug\DNotImplementedException;
use app\decibel\model\field\DField;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DDefinable;
use Reflector;

/**
 * Provides advanced property reflection for Decibel.
 *
 * The DReflectionProperty class emmulates the functionality of PHP's
 * ReflectionProperty class where a property is defined using
 * a {@link app::decibel::model::field::DField DField} instance.
 *
 * @author    Timothy de Paris
 */
class DReflectionProperty implements Reflector, DDebuggable
{
    use DBaseClass;
    /**
     * Field instance describing the property.
     *
     * @var        DField
     */
    private $field;
    /**
     * Name of the property.
     *
     * @var        string
     */
    private $fieldName;
    /**
     * Reflection of the class that defines the property.
     *
     * @var        DReflectionClass
     */
    private $class;

    /**
     * Creates a reflection of the provided property.
     *
     * @param    DReflectionClass $class      Qualified name of the class that
     *                                        defines the property.
     * @param    DField           $field      Field instance describing
     *                                        the property.
     *
     * @return    static
     */
    public function __construct(DReflectionClass $class, DField $field)
    {
        $this->class = $class;
        $this->field = $field;
        $this->fieldName = $field->getName();
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'class' => $this->class->getName(),
            'name'  => $this->fieldName,
        );
    }

    /**
     * Exports a reflection.
     *
     * @warning
     * This method is currently not implemented due to a lack of PHP
     * documentation. See http://php.net/reflectionproperty.getmodifiers
     * for further information.
     *
     * @param    string $class The reflection to export,
     * @param    string $name  The property name.
     * @param    bool   $return
     *
     * @return    string
     * @throws    DNotImplementedException
     */
    public static function export($class, $name, $return = false)
    {
        throw new DNotImplementedException(array(__CLASS__, __FUNCTION__));
    }

    /**
     * Returns a reflection of the class that declared this property.
     *
     * @return    DReflectionClass
     */
    public function getDeclaringClass()
    {
        return $this->class;
    }

    /**
     * Generates and returns a doc comment for this property.
     *
     * @return    string
     */
    public function getDocComment()
    {
        $comment = "/**\n";
        $comment .= " * {$this->field->getDescription()}\n";
        $comment .= " *\n";
        $comment .= " * @var	{$this->field->getInternalDataType()}\n";
        $comment .= " */";

        return $comment;
    }

    /**
     * Returns modifiers for this property.
     *
     * @warning
     * This method is currently not implemented due to a lack of PHP
     * documentation. See http://php.net/reflectionproperty.getmodifiers
     * for further information.
     *
     * @return    int
     * @throws    DNotImplementedException
     */
    public function getModifiers()
    {
        throw new DNotImplementedException(array(__CLASS__, __FUNCTION__));
    }

    /**
     * Returns the name of this property.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->fieldName;
    }

    /**
     * Retrieves the value of this property from the provided object.
     *
     * @param    DDefinable $object The definable object.
     *
     * @return    mixed
     */
    public function getValue(DDefinable $object)
    {
        return $object->getFieldValue($this->fieldName);
    }

    /**
     * Checks whether the property has a default.
     *
     * @return    bool
     */
    public function isDefault()
    {
        return true;
    }

    /**
     * Checks if the property is private.
     *
     * @return    bool
     */
    public function isPrivate()
    {
        return false;
    }

    /**
     * Checks if the property is protected.
     *
     * @return    bool
     */
    public function isProtected()
    {
        return false;
    }

    /**
     * Checks if the property is public.
     *
     * @return    bool
     */
    public function isPublic()
    {
        return true;
    }

    /**
     * Checks if the property is static.
     *
     * @return    bool
     */
    public function isStatic()
    {
        return false;
    }

    /**
     * Sets the property to be accessible.
     *
     * @note
     * This function has no effect as all properties represented
     * by this class are public, however is implemented for consistency
     * with ReflectionProperty.
     *
     * @param    bool $accessible Whether the property should be accessible.
     *
     * @return    void
     */
    public function setAccessible($accessible)
    {
        return;
    }

    /**
     * Updates the value of this property within the provided object.
     *
     * @param    DDefinable $object The definable object to update.
     * @param    mixed      $value  The new value.
     *
     * @return    void
     */
    public function setValue(DDefinable $object, $value)
    {
        $object->setFieldValue($this->fieldName, $value);
    }

    /**
     * Returns a string representation of this reflection property.
     *
     * @return    string
     */
    public function __toString()
    {
        return '';
    }
}
