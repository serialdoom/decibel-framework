<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\index;

use app\decibel\database\mysql\DMySQL;
use app\decibel\model\field\DField;

/**
 * Represents a primary key database index required by a model.
 *
 * @author        Timothy de Paris
 */
class DPrimaryIndex extends DIndex
{
    /**
     * Creates a new DPrimaryIndex.
     *
     * @param    string $displayName Human-readable name of the index.
     *
     * @return    static
     */
    public function __construct($displayName = 'PRIMARY')
    {
        parent::__construct('PRIMARY', $displayName);
    }

    /**
     * Adds a field to this index.
     *
     * @warning
     * This method will throw a {@link DInvalidIndexFieldException} if a field
     * with a null option is added. This occurs as null fields are not valid
     * within primary keys.
     *
     * @param    DField $field Description of the field to add.
     *
     * @return    void
     * @throws    DInvalidIndexFieldException    If the field cannot be added to this index.
     */
    public function addField(DField $field)
    {
        if ($field->getNullOption() !== null) {
            throw new DInvalidIndexFieldException(
                $this,
                $field,
                '\'null\' fields are not valid for this index type.'
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
        return DMySQL::INDEX_TYPE_PRIMARY;
    }
}
