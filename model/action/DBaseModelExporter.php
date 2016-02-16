<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\action;

use app\decibel\decorator\DRuntimeDecorator;
use app\decibel\file\DCsvFile;
use app\decibel\model\DBaseModel;
use app\decibel\model\field\DField;

/**
 * Provides functionality to export {@link DBaseModel} instances.
 *
 * @author    Timothy de Paris
 */
class DBaseModelExporter extends DRuntimeDecorator
{
    /**
     * Returns the qualified name of the class that can be decorated
     * by this decorator.
     *
     * @return    string
     */
    public static function getDecoratedClass()
    {
        return DBaseModel::class;
    }

    /**
     * Returns a list of exportable fields for the model.
     *
     * @return    array    List of {@link DField} objects,
     *                    with field names as keys.
     */
    public function getExportableFields()
    {
        $fields = array();
        foreach ($this->definition->getFields() as $fieldName => $field) {
            /* @var $field DField */
            if ($field->isExportable()) {
                $fields[ $fieldName ] = $field;
            }
        }

        return $fields;
    }

    /**
     * Return a list of all exportable fields grouped by group name.
     *
     * @return    array    List of {@link DField} objects,
     *                    grouped by export group,
     *                    with field names as keys.
     */
    public function getExportableFieldsGrouped()
    {
        $result = array();
        foreach ($this->getExportableFields() as $fieldName => $field) {
            /* @var $field DField */
            $exportGroup = (string)$field->getExportGroup();
            $result[ $exportGroup ][ $fieldName ] = $field;
        }

        return $result;
    }

    /**
     * Returns a list of valid export formats for this model.
     *
     * @return    array    List of qualified names of classes extending {@link DExportFormat}
     */
    public function getExportFormats()
    {
        return array(
            DCsvFile::class,
        );
    }

    /**
     * Returns an {@link DBaseModelSearch} that can be used to export model records.
     *
     * @return    DBaseModelSearch
     */
    public function getExportSearch()
    {
        $qualifiedName = get_class($this->getDecorated());

        return $qualifiedName::search();
    }
}
