<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\cache\DCacheHandler;
use app\decibel\cache\debug\DSerializationException;

/**
 * Provides functionality to map a {@link DUtilityDataField} object
 * to the database via SQL statements.
 *
 * @author        Timothy de Paris
 */
class DUtilityDataFieldDatabaseMapper extends DFieldDatabaseMapper
{
    /**
     * Returns the qualified name of the class that can be adapted by this adapter.
     *
     * @return    string
     */
    public static function getAdaptableClass()
    {
        return DUtilityDataField::class;
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
        $linkTo = $this->field->linkTo;
        if (is_object($data)
            && $data instanceof $linkTo
        ) {
            $serialized = serialize($data);
        } else {
            $serialized = null;
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
        $linkTo = $this->field->linkTo;
        if ($data instanceof $linkTo) {
            $unserialized = $data;
            // This could be a serialized string.
        } else {
            if (is_string($data)) {
                try {
                    $unserialized = DCacheHandler::unserialize($data);
                } catch (DSerializationException $exception) {
                    $unserialized = null;
                }
            } else {
                $unserialized = null;
            }
        }

        return $unserialized;
    }
}
