<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\database;

use app\decibel\database\schema\DSchemaElementXmlMapper;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;
use app\decibel\xml\DDOMDocument;
use app\decibel\xml\DXPath;
use DOMElement;

/**
 * Represents a manifest used to describe tables within the database.
 *
 * This class is a wrapper that should be used to access and modify
 * theme manifest XML files.
 *
 * @author    Timothy de Paris
 */
class DTableManifest extends DXPath
{
    /**
     * Create a new {@link DTableManifest} object.
     *
     * @param    DReadableStream $stream Stream containing the table manifest.
     *
     * @throws    DStreamReadException    If the stream cannot be read.
     * @return    static
     */
    public function __construct(DReadableStream $stream)
    {
        $document = DDOMDocument::create($stream);
        parent::__construct($document);
    }

    /**
     * Returns the tables defined by this manifest.
     *
     * @return    array    List of {@link DTableDefinition} objects.
     */
    public function getTableDefinitions()
    {
        // Find tables within the manifest.
        $tables = array();
        foreach ($this->query('//tables/table') as $node) {
            /* @var $node DOMElement */
            $table = new DTableDefinition();
            DSchemaElementXmlMapper::adapt($table)
                                   ->loadFromXml($node);
            $tables[ $table->getName() ] = $table;
        }

        return $tables;
    }
}
