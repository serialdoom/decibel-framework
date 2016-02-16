<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\stream;

/**
 * A stream from which CSV formatted data can be read.
 *
 * @author        Timothy de Paris
 */
interface DReadableCsvStream extends DReadableStream
{
    /**
     * Reads a line of CSV data from the stream.
     *
     * @param    string $delimiter The field delimiter used by the CSV file.
     * @param    string $enclosure The field enclosure used by the CSV file.
     * @param    string $escape    The escape character used by the CSV file.
     *
     * @return    array    The CSV data as an array, or <code>null</code>
     *                    if the end of the stream has been reached.
     * @throws    DStreamReadException    If the data could not be read.
     */
    public function readCsvLine($delimiter = ',', $enclosure = '"', $escape = '\\');
}
