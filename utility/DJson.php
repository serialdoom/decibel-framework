<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DException;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\reflection\DReflectionProperty;
use ReflectionClass;
use stdClass;

/**
 * Provides static functions to assist in the handling of JSON.
 *
 * @author    Timothy de Paris
 */
class DJson
{
    /**
     * Attempts to apply values of definable properties to the provided object.
     *
     * @param    DDefinable $definable Definable object.
     * @param    stdClass   $data      The data to apply.
     *
     * @return    void
     */
    private static function applyDefinableFieldValues(DDefinable $definable, stdClass $data)
    {
        $vars = get_object_vars($data);
        foreach ($vars as $fieldName => $value) {
            try {
                $definable->setFieldValue($fieldName, $value);
            } catch (DException $exception) {
            }
        }
    }

    /**
     * Decodes data from json format.
     *
     * This function should always be used in place of the PHP
     * <code>json_decode</code> function when decoding JSON encoded data
     * from a remote procedure, as it ensures that %Decibel objects are
     * correctly decoded.
     *
     * @param    string $json The json encoded data.
     *
     * @return    mixed    The decoded data.
     */
    public static function decode($json)
    {
        $data = json_decode($json);

        return self::unprepare($data);
    }

    /**
     * Encodes data in json format.
     *
     * This function should always be used in place of the PHP
     * <code>json_encode</code> function when returning JSON encoded data
     * from a remote procedure, as it ensures that %Decibel objects are
     * correctly encoded.
     *
     * @param    mixed $data The data to encode.
     *
     * @return    string    The json encoded data.
     */
    public static function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Recursively restores data that was previously json encoded.
     *
     * @param    mixed $data The decoded data.
     *
     * @return    mixed
     */
    public static function unprepare($data)
    {
        if (is_array($data)) {
            self::unprepareArray($data);
        }
        if (is_object($data)) {
            self::unprepareObject($data);
        }

        return $data;
    }

    /**
     * Recursively restores an array that was previously json encoded.
     *
     * @param    array $data Pointer to the data to be unprepared.
     *
     * @return    void
     */
    protected static function unprepareArray(array &$data)
    {
        foreach ($data as $key => &$value) {
            $data[ $key ] = self::unprepare($value);
        }
    }

    /**
     * Recursively restores an object that was previously json encoded.
     *
     * @param    object $data Pointer to the object to be unprepared.
     *
     * @return    void
     */
    protected static function unprepareObject(&$data)
    {
        // Decibel object.
        if (isset($data->_qualifiedName)) {
            $qualifiedName = $data->_qualifiedName;
            $reflection = new ReflectionClass($qualifiedName);
            $data = $reflection->newInstanceWithoutConstructor();
        }
        // Recurse into fields of the object.
        $hasNumericKeys = false;
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $hasNumericKeys = true;
            }
            try {
                $data->$key = self::unprepare($value);
                // Just in case this is a utility data object with invalid values.
            } catch (DInvalidFieldValueException $e) {
            }
        }
        if ($hasNumericKeys) {
            $data = get_object_vars($data);
        }
    }
}
