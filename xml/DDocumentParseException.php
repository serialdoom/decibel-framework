<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\xml;

/**
 * Handles an exception occurring when parsing an invalid document.
 *
 * @author        Timothy de Paris
 */
class DDocumentParseException extends DXmlException
{
    /**
     * Creates a new {@link DDocumentParseException}.
     *
     * @param    string $filename Name of the file in which the error occurred.
     * @param    int    $line     Line number on which the error occcurred.
     * @param    int    $column   Number of the column on which the error occurred.
     * @param    string $message  Message describing the problem.
     *
     * @return    static
     */
    public function __construct($filename, $line, $column, $message)
    {
        parent::__construct(array(
                                'filename' => $filename,
                                'line'     => $line,
                                'column'   => $column,
                                'message'  => $message,
                            ));
    }
}
