<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

/**
 * The DConfigurable interface should be implemented by any class that
 * requires manual configuration in order to function correctly.
 *
 * DConfigurable classes are linked to a {@link app::decibel::configuration::DConfiguration DConfiguration} object,
 * which is managed by the {@link app::decibel::application::DConfigurationManager DConfigurationManager}.
 *
 * @author    Timothy de Paris
 */
interface DConfigurable
{
    /**
     * Returns the qualified name of the {@link app::decibel::configuration::DConfiguration DConfiguration} class used
     * to configure this object.
     *
     * @return    string    The qualified name of the {@link app::decibel::configuration::DConfiguration
     *                      DConfiguration} class for this object, or null if no configuration is available.
     */
    public static function getConfigurationClass();

    /**
     * Returns a human-readable description for the utility.
     *
     * @return    DLabel
     */
    public static function getDescription();

    /**
     * Returns a human-readable name for the utility.
     *
     * @return    DLabel
     */
    public static function getDisplayName();

    /**
     * Determines if the application or functionality required by this wrapper
     * if currently available.
     *
     * This is used to determine available utility wrapper during application
     * installation and configuration.
     *
     * @return    bool
     */
    public static function isAvailable();

    /**
     * Determines if this utility wrapper is required by %Decibel.
     *
     * Required utility wrappers cannot be disabled within the configuration
     * for a %Decibel installation.
     *
     * @return    bool
     */
    public static function isRequired();
}
