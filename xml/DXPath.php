<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\xml;

use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;
use DOMDocument;
use DOMXPath;

/**
 * Extends PHP's DOMXPath class to provide error handling through exceptions.
 *
 * @author    Timothy de Paris
 */
class DXPath extends DOMXPath
{
    /**
     * Creates a {@link DXPath} from the provided XML content.
     *
     * @param    DReadableStream $stream Stream containing the XML content.
     *
     * @return    static
     * @throws    DStreamReadException    If the file is not readable.
     * @throws    DDocumentParseException    If the provided document contains
     *                                    invalid content.
     */
    public static function create(DReadableStream $stream)
    {
        $document = DDOMDocument::create($stream);

        return new static($document);
    }

    /**
     * Creates a {@link DXPath} from the provided HTML content.
     *
     * @param    DReadableStream $stream Stream containing the HTML content.
     *
     * @throws    DStreamReadException    If the file is not readable.
     * @throws    DDocumentParseException    If the provided document contains
     *                                    invalid content.
     * @return    static
     */
    public static function createFromHtml(DReadableStream $stream)
    {
        $document = DDOMDocument::createFromHtml($stream);

        return new static($document);
    }

    /**
     * Returns the parsed document.
     *
     * @return    DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }
}
