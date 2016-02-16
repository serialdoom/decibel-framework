<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database\schema;

use app\decibel\adapter\DAdapter;
use app\decibel\adapter\DRuntimeAdapter;
use app\decibel\xml\DDocumentParseException;
use DOMDocument;
use DOMElement;

/**
 * Provides functionality to covnert schema element definitions to and from XML.
 *
 * @author    Timothy de Paris
 */
abstract class DSchemaElementXmlMapper implements DAdapter
{
    use DRuntimeAdapter;

    /**
     * Returns an XML representation of this schema element.
     *
     * @param    DOMDocument $document Document from which the node will be created.
     *
     * @return    DOMElement
     */
    abstract public function getAsXml(DOMDocument $document);

    /**
     * Loads values for this schema element from the provided DOMElement.
     *
     * @param    DOMElement $node XML schema element definition.
     *
     * @return    void
     * @throws    DDocumentParseException    If the provided    DOMElement is malformed.
     */
    abstract public function loadFromXml(DOMElement $node);
}
