<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\reflection;

/**
 * Denotes a class as having the ability to be described by
 * a {@link DReflectionClass} class.
 *
 * @author        Timothy de Paris
 */
interface DReflectable
{
    /**
     * Returns a human-readable description for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDescription();

    /**
     * Returns a human-readable name for the configurable object.
     *
     * @return    DLabel
     */
    public static function getDisplayName();

    /**
     * Provides a reflection of this class.
     *
     * @return    DReflectionClass
     */
    public static function getReflection();
}
