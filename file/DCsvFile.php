<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use app\decibel\debug\DErrorHandler;
use app\decibel\file\DExportFormat;
use app\decibel\regional\DLabel;
use app\decibel\stream\DReadableCsvStream;
use app\decibel\stream\DStreamReadException;
use app\decibel\stream\DWritableStream;

/**
 * Defines a CSV (comma seperated values) file.
 *
 * @author    Nikolay Dimitrov
 */
class DCsvFile implements DExportFormat
{
    /**
     * Associative array key used for the CSV header.
     *
     * @var        string
     */
    const KEY_HEADER = 'header';

    /**
     * The stream the export is to be written to.
     *
     * @var        DWritableStream
     */
    private $stream;

    /**
     * Returns the human-readable name of the export format.
     *
     * @return    DLabel
     */
    public function getExportFormatName()
    {
        return 'CSV';
    }

    /**
     * Initialises the export.
     *
     * @param    DWritableStream $stream      Stream into which the export
     *                                        will be written.
     * @param    array           $fieldNames  Names of the fields being exported.
     *
     * @return    void
     */
    public function startExport(DWritableStream $stream, array $fieldNames)
    {
        // Don't dump debugging information.
        DErrorHandler::allowDump(false);
        // Store the stream for subsequent row exports.
        $this->stream = $stream;
        // Genereate CSV header row.
        $header = '"' . implode('","', $fieldNames) . "\"\n";
        // Write header to the stream.
        $this->stream->write($header);
    }

    /**
     * Adds a row of data to the export.
     *
     * @param    array $data      Associative array containing data for the row,
     *                            with field names as keys.
     *
     * @return    void
     */
    public function exportRow(array $data)
    {
        // Do not allow empty quotes
        foreach ($data as $key => $value) {
            if ($value !== '') {
                $value = iconv("UTF-8", "UTF-16LE//IGNORE", $value);
                $data[ $key ] = '"' . str_replace('"', '""', $value) . '"';
            }
        }
        // Generate CSV row.
        $row = implode(',', $data) . "\n";
        // Write row to the stream.
        $this->stream->write($row);
    }

    /**
     * Finalises the export.
     *
     * @return    void
     */
    public function endExport()
    {
        // Close the stream.
        $this->stream->close();
    }

    /**
     * Imports CSV data into an array.
     *
     * @note
     * This method assumes CSV files are valid and does not perform any checks.
     *
     * @param    DReadableCsvStream $stream    Stream from which to read the CSV content.
     *                                         For backwards compatibility, a filename
     *                                         may also be passed, however this
     *                                         is deprecated behaviour.
     * @param    bool               $header    Whether the CSV file has a header.
     * @param    string             $delimiter The field delimiter used by the CSV file.
     * @param    string             $enclosure The field enclosure used by the CSV file.
     * @param    string             $escape    The escape character used by the CSV file.
     *
     * @return    array
     * @throws    DStreamReadException    If an error occurs reading from the stream.
     */
    public static function parse(DReadableCsvStream $stream, $header = true,
                                 $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $data = array();
        $headerWritten = !$header;
        while (($line = $stream->readCsvLine($delimiter, $enclosure, $escape)) !== null) {
            // Handle the header row if required.
            if (!$headerWritten) {
                $data[ self::KEY_HEADER ] = $line;
                $headerWritten = true;
                // Handle additional rows.
            } else {
                if ($header) {
                    $data[] = array_combine($data[ self::KEY_HEADER ], $line);
                } else {
                    $data[] = $line;
                }
            }
        }

        return $data;
    }
}
