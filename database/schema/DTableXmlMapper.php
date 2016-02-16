<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use DOMElement;

/**
 * Provides functionality to covnert table schmea definitions to and from XML.
 *
 * @author    Timothy de Paris
 */
class DTableXmlMapper extends DSchemaElementXmlMapper
{
    /**
     * 'name' XML attribute name.
     *
     * @var        string
     */
    const XML_ATTR_NAME = 'name';
    /**
     * 'field' XML node name.
     *
     * @var        string
     */
    const XML_NODE_FIELD = 'field';
    /**
     * 'index' XML node name.
     *
     * @var        string
     */
    const XML_NODE_INDEX = 'index';
    /**
     * 'table' XML node name.
     *
     * @var        string
     */
    const XML_NODE_TABLE = 'table';

    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DTableDefinition::class;
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
        $element = $document->createElement(self::XML_NODE_TABLE);
        $element->setAttribute(self::XML_ATTR_NAME, $this->adaptee->getName());
        foreach ($this->adaptee->getColumns() as $column) {
            /* @var $column DColumnDefinition */
            $node = DSchemaElementXmlMapper::adapt($column)
                                           ->getAsXml($document);
            $element->appendChild($node);
        }
        foreach ($this->adaptee->getIndexes() as $index) {
            /* @var $index DIndexDefinition */
            $node = DSchemaElementXmlMapper::adapt($index)
                                           ->getAsXml($document);
            $element->appendChild($node);
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
        foreach ($node->childNodes as $childNode) {
            /* @var $childNode DOMElement */
            $nodeName = $childNode->nodeName;
            if ($nodeName === self::XML_NODE_FIELD) {
                $column = new DColumnDefinition();
                DSchemaElementXmlMapper::adapt($column)
                                       ->loadFromXml($childNode);
                $this->adaptee->addColumn($column);
            } else {
                if ($nodeName === self::XML_NODE_INDEX) {
                    $index = new DIndexDefinition();
                    DSchemaElementXmlMapper::adapt($index)
                                           ->loadFromXml($childNode);
                    $this->adaptee->addIndex($index);
                } else {
                    // Any other nodes will be ignored.
                    // This could include text nodes, comment nodes, or nodes with other names.
                }
            }
        }
    }
}
