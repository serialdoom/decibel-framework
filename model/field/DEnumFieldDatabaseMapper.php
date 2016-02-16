<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Provides functionality to map a {@link DEnumField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DEnumFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DEnumField::class;
    }

    /**
     * Restores data from serialised form in the database.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        // Just in case an option has been removed
        // but is still stored in the database...
        if (!$this->field->isValidValue($data)) {
            $unserialized = null;
        } else {
            $unserialized = parent::unserialize($data);
        }

        return $unserialized;
    }
}
