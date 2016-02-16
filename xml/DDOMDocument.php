<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\xml;

use app\decibel\stream\DReadableStream;
use app\decibel\stream\DStreamReadException;
use DOMDocument;

/**
 * Extends PHP's DOMDocument class to provide error handling through exceptions.
 *
 * @author    Timothy de Paris
 */
class DDOMDocument extends DOMDocument
{
    /**
     * Creates a {@link DDOMDocument} from the provided XML content.
     *
     * @param    DReadableStream $stream  Stream containing the XML content.
     * @param    int             $options Libxml options.
     *
     * @return    DDOMDocument
     * @throws    DStreamReadException    If the stream is not readable.
     * @throws    DDocumentParseException    If the provided document contains
     *                                    invalid content.
     */
    public static function create(DReadableStream $stream, $options = 0)
    {
        // Ensure errors are not triggered
        $loadOptions = $options | LIBXML_NOERROR | LIBXML_NOWARNING;
        // Clear XML error buffer in case an error already exists.
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        // Load manifest XML.
        $document = new DOMDocument();
        $success = $document->loadXML($stream->read(), $loadOptions);
        if ($success === false) {
            DDOMDocument::handleParseError();
        }

        return $document;
    }

    /**
     * Creates a {@link DDOMDocument} from the provided HTML content.
     *
     * @param    DReadableStream $stream      Stream containing the HTML content.
     * @param    int             $options     Libxml options
     *                                        (see http://php.net/manual/en/libxml.constants.php)
     *
     * @throws    DStreamReadException    If the stream is not readable.
     * @throws    DDocumentParseException    If the provided document contains
     *                                    invalid content.
     * @return    DDOMDocument
     */
    public static function createFromHtml(DReadableStream $stream, $options = 0)
    {
        // Ensure errors are not triggered
        $loadOptions = $options | LIBXML_NOERROR | LIBXML_NOWARNING;
        // Clear XML error buffer in case an error already exists.
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        // Load manifest XML.
        $document = new DOMDocument();
        $success = $document->loadHTML($stream->read(), $loadOptions);
        if ($success === false) {
            // We haven't been able to provide HTML malformed enough to make
            // this occur, so this isn't covered in tests, however it has been
            // left to handle the case where malformed HTML is detected.
            DDOMDocument::handleParseError();
        }

        return $document;
    }

    /**
     * Converts any parse errors into exceptions.
     *
     * @throws    DDocumentParseException    If a parse error has been generated
     *                                    by the DOMDocument class.
     * @return    void
     */
    protected static function handleParseError()
    {
        // Check for XML parsing errors.
        $error = libxml_get_last_error();
        if ($error) {
            throw new DDocumentParseException(
                $error->file,
                $error->line,
                $error->column,
                $error->message
            );
        }
    }
}
