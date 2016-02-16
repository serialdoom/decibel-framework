<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\regional\DLabel;
use app\decibel\regional\DUnknownLabelException;

/**
 * A class that has human-readable descriptions.
 *
 * @author        Timothy de Paris
 */
trait DDescribableObject
{
    /**
     * Returns a human-readable name for this object type.
     *
     * @note
     * To have a description, extending classes should define a label with the namespace
     * of the extending class and the name 'description'.
     *
     * @return    DLabel    The description, or <code>null</code> if no description
     *                    if available for this object type.
     */
    public static function getDescription()
    {
        try {
            $label = new DLabel(get_called_class(), DDescribable::LABEL_DESCRIPTION);
        } catch (DUnknownLabelException $exception) {
            $label = null;
        }

        return $label;
    }

    /**
     * Returns a human-readable name for this object type.
     *
     * @note
     * To have a human-readable name, extending classes should define a label with the namespace
     * of the extending class and the name 'displayName'. If no label is found, the unqualified
     * class name will be used.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        $class = get_called_class();
        try {
            $label = new DLabel($class, DDescribable::LABEL_DISPLAY_NAME);
        } catch (DUnknownLabelException $exception) {
            $label = substr(strrchr($class, '\\'), 1);
        }

        return $label;
    }

    /**
     * Returns a human-readable plural form name for the object type.
     *
     * @note
     * To have a human-readable name, extending classes should define a label with the namespace
     * of the extending class and the name 'displayNamePlural'. If no label is found, the unqualified
     * class name will be used.
     *
     * @return    DLabel
     */
    public static function getDisplayNamePlural()
    {
        $class = get_called_class();
        try {
            $label = new DLabel($class, DDescribable::LABEL_DISPLAY_NAME_PLURAL);
        } catch (DUnknownLabelException $exception) {
            $label = static::getDisplayName();
        }

        return $label;
    }
}
