<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\model\field\DField;
use DOMDocument;
use DOMElement;

/**
 * Provides functionality to covnert column schmea definitions to and from XML.
 *
 * @author    Timothy de Paris
 */
class DColumnXmlMapper extends DSchemaElementXmlMapper
{
    /**
     * 'autoincrement' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_AUTOINCREMENT = 'autoincrement';

    /**
     * 'defaultValue' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_DEFAULT_VALUE = 'defaultValue';

    /**
     * 'name' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_NAME = 'name';

    /**
     * 'null' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_NULL = 'null';

    /**
     * 'size' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_SIZE = 'size';

    /**
     * 'type' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_TYPE = 'type';

    /**
     * 'unsigned' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_UNSIGNED = 'unsigned';

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DColumnDefinition::class;
    }

    /**
     * Returns an XML representation of this schema element.
     *
     * @param    DOMDocument $document Document from which the node will be created.
     *
     * @return    DOMElement
     */
    public function getAsXml(DOMDocument $document)
    {
        $element = $document->createElement(DTableXmlMapper::XML_NODE_FIELD);
        $element->setAttribute(self::XML_ATTR_NAME, $this->adaptee->getFieldValue(self::FIELD_NAME));
        $element->setAttribute(self::XML_ATTR_TYPE, $this->adaptee->getFieldValue(self::FIELD_TYPE));
        $size = $this->adaptee->getFieldValue(self::FIELD_SIZE);
        if ($size) {
            $element->setAttribute(self::XML_ATTR_SIZE, $size);
        }
        $defaultValue = $this->adaptee->getDefaultValue();
        if ($defaultValue !== null) {
            $element->setAttribute(self::XML_ATTR_DEFAULT_VALUE, $defaultValue);
        }
        if ($this->adaptee->getFieldValue(self::FIELD_UNSIGNED)) {
            $element->setAttribute(self::XML_ATTR_UNSIGNED, 'true');
        }
        if ($this->adaptee->getFieldValue(self::FIELD_NULL)) {
            $element->setAttribute(self::XML_ATTR_NULL, 'true');
        }

        return $element;
    }

    /**
     * Loads values for this schema element from the provided DOMElement.
     *
     * @note
     * Invalid nodes and attributes within the XML element will be ignored.
     *
     * @param    DOMElement $node XML schema element definition.
     *
     * @return    void
     */
    public function loadFromXml(DOMElement $node)
    {
        $this->adaptee->resetFieldValues();
        $name = $node->getAttribute(self::XML_ATTR_NAME);
        $this->adaptee->setName($name);
        $this->adaptee->setType($node->getAttribute(self::XML_ATTR_TYPE));
        // NULL values allowed?
        $null = $node->hasAttribute(self::XML_ATTR_NULL)
            && $node->getAttribute(self::XML_ATTR_NULL) === 'true';
        $this->adaptee->setNull($null);
        // Column is unsigned?
        $this->adaptee->setUnsigned($node->hasAttribute(self::XML_ATTR_UNSIGNED));
        // Autoincrement enabled?
        $autoIncrement = $node->hasAttribute(self::XML_ATTR_AUTOINCREMENT)
            && $node->getAttribute(self::XML_ATTR_AUTOINCREMENT) === 'true';
        $this->adaptee->setAutoincrement($autoIncrement);
        // Apply a default value if there is one.
        if ($node->hasAttribute(self::XML_ATTR_DEFAULT_VALUE)) {
            $defaultValue = $node->getAttribute(self::XML_ATTR_DEFAULT_VALUE);
            $castDefaultValue = $this->adaptee->castValueForField($defaultValue);
            $this->adaptee->setDefaultValue($castDefaultValue);
        }
        // Use size only if it's not any type of int field
        if (stripos($this->adaptee->getFieldValue(DColumnDefinition::FIELD_TYPE), DField::DATA_TYPE_INT) === false
            && $node->hasAttribute(self::XML_ATTR_SIZE)
        ) {
            $this->adaptee->setSize((int)$node->getAttribute(self::XML_ATTR_SIZE));
        }
    }
}
