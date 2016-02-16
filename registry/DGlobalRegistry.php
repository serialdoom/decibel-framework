<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\application\DApp;
use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\Decibel;
use app\decibel\file\DDirectoryIterator;
use SplFileInfo;

/**
 * Provides a registry of information about all installed Apps.
 *
 * @author        Timothy de Paris
 */
class DGlobalRegistry extends DRegistry
{
    /**
     * Caches the global registry in process memory after first loaded.
     *
     * @var        DGlobalRegistry
     */
    private static $registry;

    /**
     * Caches App registries in process memory after first loaded.
     *
     * @var        array
     */
    private $appRegistries;

    /**
     * Loads the global registry.
     *
     * @return    DGlobalRegistry
     */
    protected function __construct()
    {
        parent::__construct(DECIBEL_PATH, DECIBEL_REGISTRY_PATH . 'app');
    }

    /**
     * Compares the load priority of two Apps for array sorting purposes.
     *
     * @param    DApp $app1 The first App.
     * @param    DApp $app2 The second App.
     *
     * @return    int
     */
    private function compareLoadPriority(DApp $app1, DApp $app2)
    {
        if ($app1->getLoadPriority() < $app2->getLoadPriority()) {
            $compare = -1;
        } else {
            if ($app1->getLoadPriority() > $app2->getLoadPriority()) {
                $compare = 1;
            } else {
                $compare = 0;
            }
        }

        return $compare;
    }

    /**
     * Returns the registries for all installed Apps.
     *
     * @return    array    List of {@link DRegistry} objects.
     */
    public function getAppRegistries()
    {
        if ($this->appRegistries === null) {
            $this->appRegistries = $this->loadAppRegistries();
        }

        return $this->appRegistries;
    }

    /**
     * Retrieves a hive from the registry.
     *
     * @note
     * This method overrides {@link DRegistry::getHive()} to ensure that
     * hives from all App registries are merged into the corresponding
     * global registry hive.
     *
     * @param    string $qualifiedName Qualified name of the hive to retrieve.
     *
     * @return    DRegistryHive
     * @throws    DInvalidParameterValueException If the provided qualified name
     *                                            is not that of a registry hive.
     */
    public function getHive($qualifiedName)
    {
        if (DApplicationMode::isProductionMode()
            && $this->hasHive($qualifiedName)
        ) {
            $hive = parent::getHive($qualifiedName);
            // If the hive has not been updated in any of the
            // installed App registries, just return it.
        } else {
            if ($this->isHiveLoaded($qualifiedName)
                || !$this->isHiveUpdated($qualifiedName)
            ) {
                $hive = parent::getHive($qualifiedName);
                // Otherwise we need to rebuild the hive.
            } else {
                /* @var $hive DRegistryHive */
                $hive = new $qualifiedName($this);
                $appRegistries = $this->getAppRegistries();
                foreach ($appRegistries as $registry) {
                    /* @var $registry DRegistry */
                    $appHive = $registry->getHive($qualifiedName);
                    $hive->merge($appHive);
                }
                // Store the merged hive.
                $this->setHive($hive);
            }
        }

        return $hive;
    }

    /**
     * Returns a list of available Apps, ordered by load priority.
     *
     * @return    array
     */
    private function getPrioritisedApps()
    {
        // support decibel in Standalone mode
        if (DApplicationMode::isTestMode()) {
            $appQualifiedName = Decibel::class;
            /** @var Decibel $app */
            $app = new $appQualifiedName();
            $apps = [ $app->setRelativePath('') ];
        } else {
            $apps = array();
            $path = DECIBEL_PATH . 'app/';
            $iterator = DDirectoryIterator::getIterator($path);
            foreach ($iterator as $directory) {
                /* @var $directory SplFileInfo */
                $directoryName = $directory->getFilename();
                $appName = ucfirst($directoryName);
                $appQualifiedName = "app\\{$directoryName}\\{$appName}";
                if (!class_exists($appQualifiedName)
                    || !is_subclass_of($appQualifiedName, DApp::class)
                ) {
                    continue;
                }
                $apps[] = new $appQualifiedName();
            }
            // Sort the Apps in priority order, to ensure correct loading of
            // configuration and registration information.
            uasort($apps, array(self::class, 'compareLoadPriority'));
        }
        return $apps;
    }

    /**
     * Determines if the specified hive has been updated in any
     * of the available App registries.
     *
     * @param    string $qualifiedName Qualified name of the hive to retrieve.
     *
     * @return    bool
     * @throws    DInvalidParameterValueException If the provided qualified name
     *                                            is not that of a registry hive.
     */
    public function isHiveUpdated($qualifiedName)
    {
        // Hive is already loaded, so can't require update.
        if ($this->isHiveLoaded($qualifiedName)) {
            $updated = false;
        }
        // Hive has not been created yet.
        if (!$this->hasHive($qualifiedName)) {
            $updated = true;
            // Check each of the App registries to determine if they have been rebuilt
            // (only if we are able to rebuild this registry though).
        } else {
            if ($this->canRebuild()) {
                $updated = false;
                $appRegistries = $this->getAppRegistries();
                foreach ($appRegistries as $registry) {
                    /* @var $registry DRegistry */
                    $appHive = $registry->getHive($qualifiedName);
                    if ($appHive->isUpdated()) {
                        $updated = true;
                        break;
                    }
                }
            } else {
                $updated = false;
            }
        }

        return $updated;
    }

    /**
     * Loads the global registry.
     *
     * @return    DGlobalRegistry
     */
    public static function load()
    {
        if (self::$registry === null) {
            self::$registry = new DGlobalRegistry();
        }

        return self::$registry;
    }

    /**
     * Loads registries for all installed Apps.
     *
     * @return    array    List of {@link DRegistry} objects.
     */
    private function loadAppRegistries()
    {
        $registries = array();
        foreach ($this->getPrioritisedApps() as $app) {
            /* @var DApp $app */
            $registries[] = DAppRegistry::load($app);
        }

        return $registries;
    }
}
