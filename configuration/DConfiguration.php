<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\authorise\DAuthorisationManager;
use app\decibel\authorise\DPrivilege;
use app\decibel\authorise\debug\DUnprivilegedException;
use app\decibel\debug\DDebuggable;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\utility\DDefinable;
use app\decibel\utility\DDefinableCache;
use app\decibel\utility\DDefinableObject;
use app\decibel\utility\DPharHive;
use app\decibel\utility\DSingleton;

/**
 * Base class for configuration objects.
 *
 * @author        Timothy de Paris
 */
abstract class DConfiguration implements DDispatchable, DPharHive,
                                         DDefinable, DSingleton, DDebuggable
{
    use DDefinableObject;
    use DDefinableCache;
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
     * Stores references to loaded configurations.
     *
     * @var        array
     */
    protected static $instances = array();

    /**
     * Creates a {@link DConfiguration} object.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->loadDefinitions();
    }

    /**
     * Specifies which fields will be stored in the serialized object.
     *
     * @return    array    List containing names of the fields to serialize.
     */
    public function __sleep()
    {
        return array(
            'fieldValues',
        );
    }

    /**
     * Restores the object following unserialization.
     *
     * @return    array
     */
    public function __wakeup()
    {
        $this->loadDefinitions();
    }

    /**
     * Defines fields available for this configuration.
     *
     * This function should call the {@link DConfiguration::addField()} function.
     *
     * @return    void
     */
    abstract protected function define();

    /**
     * Returns the {@link DConfigurationStoreInterface} in which this configuration is stored.
     *
     * @return DConfigurationStoreInterface
     */
    public static function getConfigurationStore()
    {
        return DDefaultConfigurationStore::load();
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
     * Returns the privilege required in order to save changes to this configuration hive.
     *
     * @return    string
     */
    public static function getRequiredPrivilege()
    {
        return DPrivilege::ROOT;
    }

    /**
     * Loads a singleton instance of this configuration from its configuration store.
     *
     * @return    static
     */
    public static function load()
    {
        $qualifiedName = get_called_class();
        // Check the local cache first.
        if (!isset(static::$instances[ $qualifiedName ])) {
            $store = static::getConfigurationStore();
            $configuration = $store->getHive($qualifiedName);
            if (!$configuration) {
                $configuration = new $qualifiedName();
            }
            // Store the reference internally. This is important as if the configuration
            // has been newly created (i.e. wasn't prevously in the store), the store
            // won't hold a reference so we could end up with multiple copied.
            static::$instances[ $qualifiedName ] = $configuration;
        }

        return static::$instances[ $qualifiedName ];
    }

    /**
     * Saves the configuration to its configuration store.
     *
     * @note
     * Calling this method will trigger the {@link DOnConfigurationChange}
     * event for the saved {@link DConfiguration} class.
     *
     * @return    bool
     * @throws    DUnprivilegedException    If the current user does not have the correct privilege
     *                                    to save changes to this configuration, as determined
     *                                    by {@link DConfiguration::getRequiredPrivilege()}
     */
    public function save()
    {
        $user = DAuthorisationManager::getResponsibleUser();
        $privilege = static::getRequiredPrivilege();
        if (!$user->hasPrivilege($privilege)) {
            throw new DUnprivilegedException($user, $privilege);
        }
        $store = static::getConfigurationStore();
        $result = $store->setHive($this);
        if ($result) {
            $onChange = new DOnConfigurationChange();
            $this->notifyObservers($onChange);
        }

        return $result;
    }
}
