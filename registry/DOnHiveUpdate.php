<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\event\DEvent;
use app\decibel\regional\DLabel;

/**
 * Event triggered when a registry hive is updated.
 *
 * @author        Timothy de Paris
 */
class DOnHiveUpdate extends DEvent
{
    ///@cond INTERNAL
    /**
     * The registry hive that was updated.
     *
     * @var        DRegistryHive
     */
    protected $hive;

    ///@endcond
    /**
     * Defines parameters available for this event.
     *
     * @return    void
     */
    protected function define()
    { }

    /**
     * Returns a human-readable description for the event.
     *
     * @return    DLabel
     */
    public static function getDescription()
    {
        return null;
    }

    /**
     * Returns a human-readable name for the event.
     *
     * @return    DLabel
     */
    public static function getDisplayName()
    {
        return null;
    }

    /**
     * Returns the hive that was updated.
     *
     * @return    DRegistryHive
     */
    public function getHive()
    {
        return $this->hive;
    }

    /**
     * Sets the hive that was updated.
     *
     * @param    DRegistryHive $hive The hive that was updated.
     *
     * @return    static
     */
    public function setHive(DRegistryHive $hive)
    {
        $this->hive = $hive;

        return $this;
    }
}
