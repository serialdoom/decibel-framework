<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * A field that can be included in an export.
 *
 * @author    Timothy de Paris
 */
interface DExportable
{
    /**
     * Returns an array containing the names of columns that will be returned
     * when exporting this field.
     *
     * @return    array
     */
    public function getExportColumns();

    /**
     * Returns the name of the group in which this field should be included for exports.
     *
     * @return    DLabel
     */
    public function getExportGroup();

    /**
     * Determines if this field is exportable.
     *
     * @return    bool
     */
    public function isExportable();

    /**
     * Determines if this field is selected for export by default.
     *
     * @return    bool
     */
    public function isSelectedForExport();
}

