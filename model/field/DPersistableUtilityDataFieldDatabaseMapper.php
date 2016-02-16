<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model\field;

use app\decibel\debug\DInvalidPropertyException;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DReadOnlyParameterException;
use app\decibel\utility\DUtilityData;

/**
 * Provides functionality to map a {@link DUtilityDataField} object
 * that manages {@link DPersistableFieldData} objects to the database
 * via SQL statements.
 *
 * @author        Timothy de Paris
 */
abstract class DPersistableUtilityDataFieldDatabaseMapper extends DUtilityDataFieldDatabaseMapper
{
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
            $serialized = $data->toArray();
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
        if ($data instanceof DUtilityData) {
            $unserialized = $data;
            // Construct object from array values.
        } else {
            if (is_array($data)) {
                $link = $this->field->linkTo;
                try {
                    $unserialized = $link::fromArray($data);
                } catch (DReadOnlyParameterException $exception) {
                    $unserialized = null;
                } catch (DInvalidParameterValueException $exception) {
                    $unserialized = null;
                } catch (DInvalidPropertyException $exception) {
                    $unserialized = null;
                }
            } else {
                $unserialized = null;
            }
        }

        return $unserialized;
    }
}
