<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

/**
 * Defines a group of {@link DConfigurable} objects.
 *
 * @author    Timothy de Paris
 */
interface DConfigurableSet
{
    /**
     * Determines if multiple selected {@link DConfigurable} objects can be priority
     * ordered for this configurable set.
     *
     * @return    bool
     */
    public static function hasPriorityOrder();

    /**
     * Determines if multiple {@link DConfigurable} objects can be selected consecutively
     * for ths configurable set.
     *
     * @return    bool
     */
    public static function hasMultipleOptions();
}
