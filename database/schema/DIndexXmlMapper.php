<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use DOMDocument;
use DOMElement;

/**
 * Provides functionality to covnert index schmea definitions to and from XML.
 *
 * @author    Timothy de Paris
 */
class DIndexXmlMapper extends DSchemaElementXmlMapper
{
    /**
     * 'fields' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_COLUMNS = 'fields';

    /**
     * 'name' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_NAME = 'name';

    /**
     * 'type' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_TYPE = 'type';

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DIndexDefinition::class;
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
        $element = $document->createElement(DTableXmlMapper::XML_NODE_INDEX);
        $element->setAttribute(self::XML_ATTR_TYPE, $this->adaptee->getFieldValue(self::FIELD_TYPE));
        $element->setAttribute(self::XML_ATTR_NAME, $this->adaptee->getFieldValue(self::FIELD_NAME));
        $element->setAttribute(self::XML_ATTR_FIELDS, implode(',', $this->adaptee->getColumns()));

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
        $type = $node->getAttribute(self::XML_ATTR_TYPE);
        $this->adaptee->setType($type);
        $columns = explode(',', $node->getAttribute(self::XML_ATTR_COLUMNS));
        $this->adaptee->setColumns($columns);
    }
}
