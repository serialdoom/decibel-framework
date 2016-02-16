<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DException;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\regional\DLabel;

/**
 * Implementation of the {@link DExportable} interface for {@link DField} objects.
 *
 * @author    Timothy de Paris
 */
trait DExportableField
{
    /**
     * Determines if this field will be included in the standard object export.
     *
     * @var        bool
     */
    protected $exportable = true;
    /**
     * Allows grouping of exportable fields.
     *
     * @var        string
     */
    protected $exportGroup;
    /**
     * Whether the field will be selected by default for exports.
     *
     * @var        bool
     */
    protected $exportSelected;

    /**
     * Returns an array containing the names of columns that will be returned
     * when exporting this field.
     *
     * @return    array
     */
    public function getExportColumns()
    {
        return array($this->name);
    }

    /**
     * Returns the name of the group in which this field should be included for exports.
     *
     * @return    DLabel
     */
    public function getExportGroup()
    {
        if ($this->exportGroup === null) {
            try {
                $addedBy = $this->addedBy;
                $this->exportGroup = "{$addedBy::getDisplayName()} Properties";
            } catch (DException $exception) {
                $this->exportGroup = 'Additional Properties';
            }
        }

        return $this->exportGroup;
    }

    /**
     * Determines if this field is exportable.
     *
     * @return    bool
     */
    public function isExportable()
    {
        return $this->exportable;
    }

    /**
     * Determines if this field is selected for export by default.
     *
     * @return    bool
     */
    public function isSelectedForExport()
    {
        if ($this->exportSelected === null) {
            $this->exportSelected = ($this->addedBy === $this->owner);
        }

        return $this->exportSelected;
    }

    /**
     * Sets whether data for this field is exportable.
     *
     * @param    bool $exportable Whether data for this field is exportable.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    static
     */
    public function setExportable($exportable)
    {
        $this->setBoolean('exportable', $exportable);

        return $this;
    }

    /**
     * Sets the name of the group in which this field will be placed for exports.
     *
     * @param    DLabel $exportGroup The export group name.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    static
     */
    public function setExportGroup($exportGroup)
    {
        $this->setLabel('exportGroup', $exportGroup);

        return $this;
    }

    /**
     * Sets whether this field will be selected by default for exports.
     *
     * @param    bool $exportSelected Whether this field is selected by default.
     *
     * @throws    DInvalidParameterValueException    If the parameter value is invalid.
     * @return    static
     */
    public function setExportSelected($exportSelected)
    {
        $this->setBoolean('exportSelected', $exportSelected);

        return $this;
    }
}
