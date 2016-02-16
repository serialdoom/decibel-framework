<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * A class that has human-readable descriptions.
 *
 * @author        Timothy de Paris
 */
interface DDescribable
{
    /**
     * Name of the description label for describable objects.
     *
     * @var        string
     */
    const LABEL_DESCRIPTION = 'description';
    /**
     * Name of the display name label for describable objects.
     *
     * @var        string
     */
    const LABEL_DISPLAY_NAME = 'displayName';
    /**
     * Name of the plural display name label for describable objects.
     *
     * @var        string
     */
    const LABEL_DISPLAY_NAME_PLURAL = 'displayNamePlural';

    /**
     * Returns a human-readable name for this object type.
     *
     * @return    DLabel    The description, or <code>null</code> if no description
     *                    if available for this object type.
     */
    public static function getDescription();

    /**
     * Returns a human-readable name for this object type.
     *
     * @return    DLabel
     */
    public static function getDisplayName();

    /**
     * Returns a human-readable plural form name for the object type.
     *
     * @return    DLabel
     */
    public static function getDisplayNamePlural();
}
