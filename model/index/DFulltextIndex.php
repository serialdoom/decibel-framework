<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\model\index;

use app\decibel\database\mysql\DMySQL;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\model\field\DField;
use app\decibel\model\field\DTextField;

/**
 * Represents a fulltext database index required by a model.
 *
 * Only fields that contain textual content can be added to an index of this type.
 *
 * @author        Timothy de Paris
 */
class DFulltextIndex extends DIndex
{
    /**
     * Adds a field to this index.
     *
     * Fields added to a {@link DFulltextIndex} must contain textual content,
     * otherwise an exception will be triggered.
     *
     * @param    DField $field Pointer to the {@link app::decibel::model::field::DField DField} object
     *                            describing the field to add.
     *
     * @return    void
     * @throws    DInvalidIndexFieldException    If the field cannot be added to this index.
     */
    public function addField(DField $field)
    {
        // Validate field addition.
        if (!$field instanceof DTextField) {
            throw new DInvalidIndexFieldException(
                $this,
                $field,
                'Non-textual fields are not valid for this index type.'
            );
        }

        return parent::addField($field);
    }

    /**
     * Returns the index type name as used by the database.
     *
     * @return    string
     */
    public function getDatabaseIdentifier()
    {
        return DMySQL::INDEX_TYPE_FULLTEXT;
    }
}
