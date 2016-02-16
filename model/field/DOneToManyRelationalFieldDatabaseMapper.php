<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

/**
 * Provides functionality to map a {@link DOneToManyRelationalField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DOneToManyRelationalFieldDatabaseMapper extends DOneToOneRelationalFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DOneToManyRelationalField::class;
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
        // This could be an array of IDs or DPersistable instances.
        if (is_array($data)) {
            $serialized = array();
            foreach ($data as $value) {
                $value = parent::serialize($value);
                if ($value) {
                    $serialized[] = $value;
                }
            }
            // Anything else should not be returned.
        } else {
            $serialized = array();
        }

        return $serialized;
    }

    /**
     * Restores data from its serialised form in the database.
     *
     * @param    mixed $data The data to unserialize.
     *
     * @return    mixed    The unserialized data.
     */
    public function unserialize($data)
    {
        if (is_array($data)) {
            $unserialized = array();
            foreach ($data as $value) {
                $value = parent::unserialize($value);
                if ($value) {
                    $unserialized[] = $value;
                }
            }
        } else {
            $unserialized = array();
        }

        return $unserialized;
    }
}
