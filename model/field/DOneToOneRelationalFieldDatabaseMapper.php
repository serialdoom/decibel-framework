<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\utility\DPersistable;

/**
 * Provides functionality to map a {@link DOneToOneRelationalField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DOneToOneRelationalFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DOneToOneRelationalField::class;
    }

    /**
     * Prepares field data for saving to the database.
     *
     * @param    mixed $data The data to serialize.
     *
     * @return    mixed    The serialized data.
     */
    public function serialize($data)
    {
        if ($data instanceof DPersistable) {
            $serialized = $data->getId();
            // Sometimes IDs are provided.
            // Really shouldn't be the case but breaks things
            // (e.g. asset field uploads) if removed.
        } else {
            if (is_numeric($data)) {
                $serialized = (int)$data;
            } else {
                $serialized = null;
            }
        }

        return $serialized;
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
        // Convert ID to model instance.
        if ($data && is_numeric($data)) {
            $unserialized = $this->field->getInstanceFromId((int)$data);
        } else {
            if ($data instanceof DPersistable) {
                $unserialized = $data;
            } else {
                $unserialized = null;
            }
        }

        return $unserialized;
    }
}
