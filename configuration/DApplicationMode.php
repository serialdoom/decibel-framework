<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\debug\DInvalidParameterValueException;

/**
 * Configuration for the Decibel application mode.
 *
 * @author    Timothy de Paris
 */
class DApplicationMode extends DConfiguration
{
    /**
     * Application mode for production.
     *
     * @var        string
     */
    const MODE_PRODUCTION = 'production';

    /**
     * Application mode for staging.
     *
     * @var        string
     */
    const MODE_TEST = 'staging';

    /**
     * Application mode for development.
     *
     * @var        string
     */
    const MODE_DEBUG = 'local';

    /**
     * Available application modes.
     *
     * @var        array
     */
    protected static $modes = array(
        self::MODE_PRODUCTION => 'Production Mode',
        self::MODE_TEST       => 'Test Mode',
        self::MODE_DEBUG      => 'Debug Mode',
    );

    /**
     * The currently configured application mode.
     *
     * @var        string
     */
    protected $mode;

    /**
     * Specifies which fields will be stored in the serialized object.
     *
     * @return    array    List containing names of the fields to serialize.
     */
    public function __sleep()
    {
        return array('mode');
    }

    /**
     * Defines fields available for this configuration.
     *
     * @return null
     */
    public function define()
    {
        return null;
    }

    /**
     * Returns the available application modes.
     *
     * @return    array
     */
    public static function getAvailableModes()
    {
        return static::$modes;
    }

    /**
     * Returns the currently configured application mode.
     *
     * @return    string
     */
    public static function getMode()
    {
        $mode = static::load()->mode;
        if (!$mode) {
            static::setMode($mode = env('APP_ENV', DApplicationMode::MODE_DEBUG));
        }
        return $mode;
    }

    /**
     * Determines if the application is currently running in debug mode.
     *
     * @return    bool
     */
    public static function isDebugMode()
    {
        return (self::getMode() === DApplicationMode::MODE_DEBUG);
    }

    /**
     * Determines if the application is currently running in production mode.
     *
     * @return    bool
     */
    public static function isProductionMode()
    {
        return (self::getMode() === DApplicationMode::MODE_PRODUCTION);
    }

    /**
     * Determines if the application is currently running in test mode.
     *
     * @return    bool
     */
    public static function isTestMode()
    {
        return (self::getMode() === DApplicationMode::MODE_TEST);
    }

    /**
     * Determines if the specified mode is valid.
     *
     * @param    string $mode
     *
     * @return    bool
     */
    public static function isValidMode($mode)
    {
        return isset(static::$modes[ $mode ]);
    }

    /**
     * Configures the application to use a different mode.
     *
     * @param    string $mode     One of:
     *                            - {@link DApplicationMode::MODE_DEBUG}
     *                            - {@link DApplicationMode::MODE_TEST}
     *                            - {@link DApplicationMode::MODE_PRODUCTION}
     *
     * @return    bool
     * @throws    DInvalidParameterValueException    If an invalid mode is provided.
     */
    public static function setMode($mode)
    {
        if (!static::isValidMode($mode)) {
            throw new DInvalidParameterValueException(
                'mode',
                array(__CLASS__, __FUNCTION__),
                'A valid application mode.'
            );
        }
        $configuration = static::load();
        $configuration->mode = $mode;

        return $configuration->save();
    }
}
