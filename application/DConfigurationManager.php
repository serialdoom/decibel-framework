<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\application;

use app\decibel\cache\DCacheHandler;
use app\decibel\configuration\DApplicationMode;
use app\decibel\configuration\DConfiguration;
use app\decibel\configuration\DConfigurationDatabaseMapper;
use app\decibel\configuration\DOnConfigurationChange;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DException;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DRecursionException;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\file\DFile;
use app\decibel\model\field\DField;
use app\decibel\registry\DClassQuery;
use app\decibel\stream\DFileStream;
use app\decibel\stream\DStreamWriteException;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DResult;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Responsible for managing configuration information for all aspects
 * of the core framework and installed Apps.
 *
 * @author         Timothy de Paris
 * @ingroup        configuration
 */
class DConfigurationManager implements DDispatchable, DSingleton, DDecoratable
{
    use DBaseClass;
    use DDecoratorCache;
    use DSingletonClass;
    use DEventDispatcher;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::configuration::DOnConfigurationChange DOnConfigurationChange}
     * event.
     *
     * @var        string
     */
    const ON_CHANGE = DOnConfigurationChange::class;

    /**
     * The internal memory limit that will be adhered to by decibel, in bytes.
     *
     * This is 80% of the server's PHP memory limit.
     *
     * @var        int
     */
    protected $internalMemoryLimit;
    /**
     * Class configuration option values.
     *
     * @var        array
     */
    protected $classValues = array();
    /**
     * List of {@link DConfiguration} objects, cached here after loaded
     * by {@link DConfigurationManager::loadConfiguration()}
     *
     * @var        array
     */
    private static $loadedConfigurations = array();
    /**
     * Determines if the cache should be cleared on saving the configuration.
     *
     * @var        bool
     */
    protected $requireCacheClear = false;

    /**
     * Loads a singleton instance of the {@link app::decibel::application::DConfigurationManager DConfigurationManager}.
     *
     * If available, the serialised {@link app::decibel::application::DConfigurationManager DConfigurationManager}
     * will be loaded from the file system.
     * @return DConfigurationManager
     * @throws DRecursionException
     */
    public static function load()
    {
        $qualifiedName = static::getSingletonClass();
        // Load if this is the first instantiation.
        if (!isset(static::$instances[ $qualifiedName ])) {
            // Note that this is loading to detect recursion.
            static::$instances[ $qualifiedName ] = true;
            // Load the instance.
            $filename = (CONFIG_PATH . 'DConfigurationManager');
            if (file_exists($filename)) {
                static::$instances[ $qualifiedName ] = unserialize(file_get_contents($filename));
            } else {
                static::$instances[ $qualifiedName ] = new $qualifiedName();
            }
        }
        // Handle recursion.
        if (static::$instances[ $qualifiedName ] === true) {
            throw new DRecursionException(array($qualifiedName, 'load'));
        }

        return static::$instances[ $qualifiedName ];
    }

    /**
     * Initialises the DConfigurationManager.
     *
     * @return    DConfigurationManager
     */
    protected function __construct()
    {
    }

    ///@cond INTERNAL
    /**
     * Returns a parameter.
     *
     * @param    string $name The name of the parameter to return.
     *
     * @return    mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $value = $this->$name;
        } else {
            $value = parent::__get($name);
        }

        return $value;
    }
    ///@endcond
    /**
     *
     * @param    string $name
     *
     * @return    array
     */
    protected function getConfigurables($name)
    {
        $configurables = DClassQuery::load()
                                    ->setAncestor('app\\decibel\\configuration\\DConfigurable')
                                    ->getClassNames();

        return array_intersect($this->$name, $configurables);
    }

    ///@cond INTERNAL
    /**
     * Defines the properties of the object that will be included when
     * serializing the object to save to the file system.
     *
     * @return    array
     */
    public function __sleep()
    {
        return array(
            'classValues',
        );
    }
    ///@endcond
    /**
     * Returns the value of a configuration option for a class.
     *
     * @param    string $for    Qualified name of the configurable class.
     * @param    string $option Name of the configuration option to return.
     *
     * @return    mixed    The configuration option value,
     *                    or <code>null</code> if not known.
     */
    public function getClassConfiguration($for, $option)
    {
        if (isset($this->classValues[ $for ][ $option ])) {
            $value = $this->classValues[ $for ][ $option ];
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * Returns the {@link app::decibel::configuration::DConfiguration DConfiguration}
     * for a specified {@link app::decibel::configuration::DConfigurable DConfigurable} object.
     *
     * @param    string $for      Qualfied name of the {@link app::decibel::configuration::DConfigurable DConfigurable}
     *                            object to return a {@link app::decibel::configuration::DConfiguration DConfiguration}
     *                            for.
     *
     * @return    DConfiguration    The {@link app::decibel::configuration::DConfiguration DConfiguration} for the
     *                              specified object, or <code>null</code> if no configuration is available for that
     *                              object.
     */
    public function getConfiguration($for)
    {
        // Validate the requested class is configurable.
        if (!DApplicationMode::isProductionMode()) {
            $this->validateConfigurationParameters($for);
        }
        // Determine qualified name of the configuration class.
        $configurationClass = $for::getConfigurationClass();
        if ($configurationClass === null) {
            $configuration = null;
        } else {
            $configuration = $this->loadConfiguration($for, $configurationClass);
        }

        return $configuration;
    }

    /**
     * Returns the qualified name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return self::ON_CHANGE;
    }

    /**
     * Returns qualified names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_CHANGE,
        );
    }

    /**
     * Returns the memory limit that will be used for internal purposes.
     *
     * @note
     * This is 80% of the configured PHP memory limit.
     *
     * @return    int        Internal memory limit in bytes.
     */
    public function getInternalMemoryLimit()
    {
        if ($this->internalMemoryLimit === null) {
            $this->internalMemoryLimit = (DFile::stringToBytes(ini_get('memory_limit')) * 0.8);
        }

        return $this->internalMemoryLimit;
    }

    /**
     * Returns configuration options able to be edited by the specified user,
     * grouped by Tab and Group in a multi-dimensional array.
     *
     * @param    string $group    The configuration option group to retrieve,
     *                            for example {@link DAppManager::CONFIG_OPTION_GENERAL}.
     *                            If not provided, options from all groups will
     *                            be returned.
     *
     * @return    array
     * @deprecated
     */
    public function getGroupedOptions($group = null)
    {
        return array();
    }

    /**
     * Loads the {@link DConfiguration} object for the specified
     * configurable class.
     *
     * @param    string $for
     * @param    string $configurationClass
     *
     * @return    DConfiguration
     */
    protected function loadConfiguration($for, $configurationClass)
    {
        if (!isset(self::$loadedConfigurations[ $for ])) {
            // Check that we have configuration information for this object.
            if (array_key_exists($configurationClass, $this->classValues)) {
                $values =& $this->classValues[ $configurationClass ];
            } else {
                $values = array();
            }
            // Apply values to the configuration.
            $configuration = $configurationClass::load();
            foreach ($values as $option => $value) {
                try {
                    $configuration->setFieldValue($option, $value);
                } catch (DException $exception) {
                }
            }
            // Cache loaded configuration internally.
            self::$loadedConfigurations[ $for ] = $configuration;
        }

        return self::$loadedConfigurations[ $for ];
    }

    /**
     * Saves the configuration to the file system.
     *
     * @note
     * All caches will be cleared if neccessary on successful saving
     * of the configuration.
     *
     * @return    DResult
     */
    public function save()
    {
        $result = new DResult();
        $filename = CONFIG_PATH . 'DConfigurationManager';
        $contents = serialize($this);
        try {
            $stream = new DFileStream($filename);
            $stream->erase();
            $stream->write($contents);
            $stream->close();
            if ($this->requireCacheClear) {
                DCacheHandler::clearCaches();
                $this->requireCacheClear = false;
            }
        } catch (DStreamWriteException $exception) {
            DErrorHandler::logException($exception);
            $result->setSuccess(DResult::TYPE_ERROR, 'Unable to write to the application configuration file.');
        }

        return $result;
    }

    /**
     * Updates the {@link app::decibel::configuration::DConfiguration DConfiguration} for a
     * specified {@link app::decibel::configuration::DConfigurable DConfigurable} object.
     *
     * @param    string         $for              Qualfied name of the {@link
     *                                            app::decibel::configuration::DConfigurable DConfigurable} object to
     *                                            update the {@link app::decibel::configuration::DConfiguration
     *                                            DConfiguration} for.
     * @param    DConfiguration $configuration    The {@link app::decibel::configuration::DConfiguration
     *                                            DConfiguration}
     *                                            for the specified object.
     *
     * @return  void
     */
    public function setConfiguration($for, DConfiguration $configuration)
    {
        // Determine qualified name of the configuration class.
        if (!DApplicationMode::isProductionMode()) {
            $this->validateConfigurationParameters($for, $configuration);
        }
        $values = array();
        foreach ($configuration->getFields() as $fieldName => $field) {
            /* @var $field DField */
            $value = $configuration->getFieldValue($fieldName);
            if ($value !== $field->getDefaultValue()) {
                $values[ $fieldName ] = $value;
            }
        }
        $this->updateClassConfigurations(get_class($configuration), $values);
        self::$loadedConfigurations[ $for ] = $configuration;
    }

    ///@cond INTERNAL
    /**
     * Updates the internal value of a class configuration option.
     *
     * @note
     * This method is public so that the {@link DConfigurationDatabaseMapper}
     * decorator can access it, however it is for internal use only.
     * The {@link DConfigurationManager::updateClassConfigurations()} method
     * should be used to update class configuration values.
     *
     * @param    string $for
     * @param    string $option
     * @param    mixed  $value
     *
     * @return    void
     */
    public function setClassConfiguration($for, $option, $value)
    {
        $this->classValues[ $for ][ $option ] = $value;
    }
    ///@endcond
    /**
     * Updates values for class configuration options.
     *
     * @param    string $for    Qualified name of the configurable class.
     * @param    array  $values List of name/value pairs.
     *
     * @return    DResult
     */
    public function updateClassConfigurations($for, array $values)
    {
        $result = new DResult();
        $databaseMapper = DConfigurationDatabaseMapper::decorate($this);
        foreach ($values as $option => $value) {
            // Update the value internally.
            $this->setClassConfiguration($for, $option, $value);
            // Update the value in the database.
            $result->merge(
                $databaseMapper->updateClassConfiguration($for, $option, $value)
            );
        }
        // Save the udpated configuration values.
        $this->requireCacheClear = true;
        $result->merge($this->save());

        return $result;
    }

    /**
     * Updates the values of one or more configuration options.
     *
     * @param    array $values List of name/value pairs.
     *
     * @return    DResult
     * @deprecated
     */
    public function updateGlobalConfiguration(array $values)
    {
    }

    /**
     * Validates parameters provided when setting or getting
     * {@link DConfiguration} object instances.
     *
     * @param    string         $for              Qualified name of the
     *                                            {@link DConfigurable} class.
     * @param    DConfiguration $configuration    The    {@link DConfiguration} object.
     *
     * @return    void
     * @throws    DInvalidParameterValueException    If the parameters are not valid.
     */
    protected function validateConfigurationParameters(
        $for, DConfiguration $configuration = null)
    {
        $valid = DClassQuery::load()
                            ->setAncestor('app\\decibel\configuration\\DConfigurable')
                            ->isValid($for);
        if (!$valid) {
            throw new DInvalidParameterValueException(
                'for',
                array(__CLASS__, __FUNCTION__),
                'Qualified name of a class extending <code>app\\decibel\\configuration\\DConfigurable</code>'
            );
        }
        if ($configuration !== null) {
            $configurationClass = $for::getConfigurationClass();
            if ($configurationClass === null
                || $configurationClass !== get_class($configuration)
            ) {
                throw new DInvalidParameterValueException(
                    'configuration',
                    array(__CLASS__, __FUNCTION__),
                    "Valid configuration for a <code>{$for}</code>"
                );
            }
        }
    }
}
