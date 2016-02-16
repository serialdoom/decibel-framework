<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\file;

use app\decibel\regional\DLabel;
use app\decibel\stream\DWritableStream;

/**
 * Defines a format in which data can be exported.
 *
 * @author        Nikolay Dimitrov
 */
interface DExportFormat
{
    /**
     * Returns the human-readable name of the export format.
     *
     * @return    DLabel
     */
    public function getExportFormatName();

    /**
     * Initialises the export.
     *
     * @param    DWritableStream $stream      Stream into which the export
     *                                        will be written.
     * @param    array           $fieldNames  Names of the fields being exported.
     *
     * @return    void
     */
    public function startExport(DWritableStream $stream, array $fieldNames);

    /**
     * Adds a row of data to the export.
     *
     * @param    array $data      Associative array containing data for the row,
     *                            with field names as keys.
     *
     * @return    void
     */
    public function exportRow(array $data);

    /**
     * Finalises the export.
     *
     * @return    void
     */
    public function endExport();
}
