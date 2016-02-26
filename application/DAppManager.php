<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\application;

use app\decibel\authorise\DPrivilege;
use app\decibel\cache\DPublicCache;
use app\decibel\database\DTableManifest;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\event\DEventDispatcher;
use app\decibel\model\DBaseModel;
use app\decibel\model\DModel;
use app\decibel\registry\DClassQuery;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\registry\DOnGlobalHiveUpdate;
use app\decibel\stream\DFileStream;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Manages Apps installed into the Decibel framework.
 *
 * @author    Timothy de Paris
 */
class DAppManager implements DSingleton
{
    use DSingletonClass;
    /**
     * 'Registrations' caching key.
     *
     * @var        string
     */
    const CACHEKEY_REGISTRATIONS = 'registrations';
    /**
     * Group for general configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_GENERAL = 'General';
    /**
     * Group for security configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_SECURITY = 'Security';
    /**
     * Group for update configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_UPDATE = 'Update';
    /**
     * Group for mail configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_MAIL = 'Mail';
    /**
     * Group for routing configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_ROUTER = 'Router';
    /**
     * Group for special configuration options.
     *
     * @var        string
     */
    const CONFIG_OPTION_SPECIAL = 'Special';

    /**
     * Pointer to the global {@link DAppInformation} registry.
     *
     * @var        DAppInformation
     */
    protected static $appInformation;
    /**
     * Contains registration information for Apps.
     *
     * @var        array
     */
    protected static $registrations;
    /**
     * List of installed Apps, cached here once {@link DApp::getApps()}
     * if first called.
     *
     * @var        array    List of {@link DApp} objects.
     */
    private static $apps;

    /**
     * Creates a new instance of the App Manager.
     *
     * @return    DAppManager
     */
    protected function __construct()
    {
        // Store the available files to optimise the autoloader.
        // This is a registry dependent cache item, so no need to worry
        // about clearing it as it will invalidate if the registry changes.
        $publicCache = DPublicCache::load();
        $availableFiles = $publicCache->retrieve(__CLASS__, 'availableFiles');
        if ($availableFiles === null) {
            $registry = DGlobalRegistry::load();
            $fileInformation = $registry->getHive(DFileInformation::class);
            $availableFiles = array_flip($fileInformation->getFiles());
            $publicCache->set(__CLASS__, 'availableFiles', $availableFiles);
        }

        // Load a list of hasing functions
        // for DECIBEL_CORE_SESSIONHASH configuration.
        if (extension_loaded('hash')) {
            $defaultHash = 'md5';
        } else {
            $defaultHash = '0';
        }
        define('DECIBEL_CORE_EXPIRYTOLERANCE', 60);
        define('DECIBEL_CORE_SESSIONHASH', $defaultHash);
        define('DECIBEL_CORE_SESSIONDOMAIN', '');
        define('DECIBEL_CORE_BLOCKIPS', false);
        define('DECIBEL_CORE_RPCPATH', 'remote/');
        define('DECIBEL_REGIONAL_DEFAULTLANGUAGE', 'en-gb');
        define('DECIBEL_REGIONAL_DATEFORMAT', 'dd/mm/yy');
        define('DECIBEL_REGIONAL_TIMEZONE', '');
        $this->loadRegistrations();
    }

    /**
     * Clears cached registration whenever the {@link DAppInformation}
     * registry is updated within the global registry.
     *
     * @param    DOnGlobalHiveUpdate $event
     *
     * @return    void
     */
    public static function clearCachedRegistrations(DOnGlobalHiveUpdate $event)
    {
        $publicCache = DPublicCache::load();
        $publicCache->remove(self::class, self::CACHEKEY_REGISTRATIONS);
    }

    /**
     *
     * @param    DOnGlobalHiveUpdate $event
     *
     * @return    void
     */
    public static function updateTableDefinitions(DOnGlobalHiveUpdate $event)
    {
        DAppManager::updateDefinitions();
    }

    /**
     * Adds registration information.
     *
     * @param    string $class    Qualified name of the registering class.
     * @param    string $type     Name of the registration type.
     * @param    mixed  $value    The registration.
     * @param    string $key      If provided, the registration will be added with
     *                            this key. If omitted, the next sequential key
     *                            will be used.
     *
     * @return    mixed    A pointer to the newly added registration information.
     */
    public static function &addRegistration($class, $type, $value, $key = null)
    {
        if ($key === null) {
            set_default(self::$registrations[ $class ][ $type ], array());
            $key = count(self::$registrations[ $class ][ $type ]);
        }
        self::$registrations[ $class ][ $type ][ $key ] = $value;

        return self::$registrations[ $class ][ $type ][ $key ];
    }

    /**
     * Return the App with the specified name.
     *
     * @param    string $name The name of the App.
     *
     * @return    DApp    The {@link DApp} object, or <code>null</code> if no
     *                    App with the specified name is installed.
     */
    public function getApp($name)
    {
        $apps = $this->getApps();
        if (isset($apps[ $name ])) {
            $app = $apps[ $name ];
        } else {
            $app = null;
        }

        return $app;
    }

    /**
     * Returns a list of installed Apps.
     *
     * @return    array    List of {@link DApp} objects.
     */
    public function getApps()
    {
        if (!self::$apps) {
            self::$apps = self::getAppInformation()->getApps();
        }

        return self::$apps;
    }

    /**
     * Returns the global App Informaton registry hive, loading
     * it from the registry if this is the first request for it.
     *
     * @return    DAppInformation
     */
    protected static function getAppInformation()
    {
        if (!self::$appInformation) {
            $registry = DGlobalRegistry::load();
            self::$appInformation = $registry->getHive(DAppInformation::class);
        }

        return self::$appInformation;
    }

    /**
     * Returns registration information.
     *
     * @param    string $class    Qualified name of the registering class.
     * @param    string $type     Name of the registration type.
     * @param    string $key      If provided, only the registration with this
     *                            key will be returned. If omitted, all registrations
     *                            of the requested type will be returned.
     *
     * @return    mixed    A pointer to the requested registration information,
     *                    or null if no information exists for the request.
     */
    public static function &getRegistration($class, $type, $key = null)
    {
        // Check this registration exists.
        if (isset(self::$registrations[ $class ][ $type ])) {
            // Return a group of registrations.
            if ($key === null) {
                $result =& self::$registrations[ $class ][ $type ];
                // Return a single registration.
            } else {
                if (isset(self::$registrations[ $class ][ $type ][ $key ])) {
                    $result =& self::$registrations[ $class ][ $type ][ $key ];
                    // Requested key does not exist.
                } else {
                    $result = null;
                }
            }
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Loads registration information into the registrations array.
     *
     * @return    bool    <code>true</code> if registrations were loaded
     *                    from files, <code>false</code> if loaded from the cache.
     */
    public function loadRegistrations()
    {
        // Check the cache for configuration options.
        $publicCache = DPublicCache::load();
        self::$registrations = $publicCache->retrieve(self::class, self::CACHEKEY_REGISTRATIONS);
        // If not available, load from .info files.
        if (self::$registrations === null) {
            // Subscribe temporary observers in case the registry is re-built.
            DAppInformation::subscribeObserver(
                array(DAppManager::class, 'clearCachedRegistrations'),
                DAppInformation::ON_GLOBAL_UPDATE
            );
            DAppInformation::subscribeObserver(
                array(DAppManager::class, 'updateTableDefinitions'),
                DAppInformation::ON_GLOBAL_UPDATE
            );
            foreach (self::getAppInformation()->getRegistrationFiles() as $filename) {
                include_once(DECIBEL_PATH . $filename);
            }
            // Register default edit privileges for models.
            $privileges =& self::$registrations[ DPrivilege::class ][ DPrivilege::REGISTRATION_PRIVILEGE ];
            DPrivilege::registerMissingPrivileges($privileges);
            // Store in cache for next time.
            $publicCache->set(self::class, self::CACHEKEY_REGISTRATIONS, self::$registrations);
            $loaded = true;
        } else {
            $loaded = false;
        }
        // Clear the internal event dispatcher cache,
        // in case an event has already been triggered.
        DEventDispatcher::clearObserverCache();

        return $loaded;
    }

    /**
     * Synchronise core table schemas as required.
     *
     * @return    void
     */
    public static function updateDefinitions()
    {
        $registry = DGlobalRegistry::load();
        $appInformation = $registry->getHive(DAppInformation::class);
        foreach ($appInformation->getTableFiles() as $manifestFile) {
            $stream = new DFileStream(DECIBEL_PATH . $manifestFile);
            $manifest = new DTableManifest($stream);
            foreach ($manifest->getTableDefinitions() as $definition) {
                /* @var $definition DTableDefinition */
                $currentTableDefinition = DTableDefinition::createFromTable($definition->getName());
                if ($currentTableDefinition) {
                    $currentTableDefinition->mergeWith($definition);
                } else {
                    $definition->createTable();
                }
            }
        }
        /** @var string Loads abstract model definitions to diff their database tables. */
        $abstractModels = DClassQuery::load()
                                     ->setAncestor(DBaseModel::class)
                                     ->setFilter(DClassQuery::FILTER_ABSTRACT)
                                     ->getClassNames();
        foreach ($abstractModels as $abstractModel) {
            /** @var DBaseModel */
            call_user_func(array($abstractModel, 'getDefinition'));
        }
    }
}
