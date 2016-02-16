<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\cache\DCacheHandler;
use app\decibel\configuration\DApplicationMode;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\file\DLocalFileSystem;
use app\decibel\registry\DRegistryHive;
use app\decibel\utility\DPharHive;
use app\decibel\utility\DPharRepository;
use Exception;
use PharData;
use UnexpectedValueException;

/**
 * Provides a registry of information about installed Apps.
 *
 * @author        Timothy de Paris
 */
abstract class DRegistry extends DPharRepository
{
    /**
     * Creates a {@link DRegistry}
     *
     * @param    string $relativePath     Path to the registry, taken from
     *                                    <code>DECIBEL_PATH</code>
     * @param    string $filename         Registry filename, to which the extension
     *                                    <code>.registry</code> will be added.
     *
     * @throws \app\decibel\file\DFileNotFoundException
     */
    protected function __construct($relativePath, $filename)
    {
        // Load the registry file.
        try {
            parent::__construct($relativePath, $filename . '.registry');
            // Handle case where the archive has become corrupted.
        } catch (UnexpectedValueException $exception) {
            $absoluteFilename = $this->getFilename();
            $fileSystem = new DLocalFileSystem();
            $fileSystem->delete($absoluteFilename);
            $this->archive = new PharData($absoluteFilename);
        }
    }

    /**
     * Determines if the registry can be rebuilt.
     *
     * @return    bool
     */
    public function canRebuild()
    {
        // Don't update the registry if not in debug mode. There shouldn't
        // be any need unless someone has been messing with the files.
        return !DApplicationMode::isProductionMode();
    }

    /**
     * Retrieves a hive from the registry.
     *
     * @param    string $qualifiedName Qualified name of the hive to retrieve.
     *
     * @return    DRegistryHive
     * @throws    DInvalidParameterValueException If the provided qualified name
     *                                            is not that of a registry hive.
     */
    public function getHive($qualifiedName)
    {
        // Validate parameters.
        if (!is_subclass_of($qualifiedName, 'app\\decibel\\registry\\DRegistryHive')) {
            throw new DInvalidParameterValueException(
                'qualifiedName',
                array(__CLASS__, __FUNCTION__),
                'Qualified name of a class extending <code>app\\decibel\\registry\\DRegistryHive</code>'
            );
        }
        // If this is the first time the hive has been
        // requested, load it from disk.
        if (!isset($this->loadedHives[ $qualifiedName ])) {
            $hive = $this->loadHive($qualifiedName);
            $this->loadedHives[ $qualifiedName ] = $hive;
        }

        return $this->loadedHives[ $qualifiedName ];
    }

    /**
     * Attempts to load a hive from the Phar archive.
     *
     * @param    string $qualifiedName Qualified name of the configuration to retrieve.
     *
     * @return    void
     */
    protected function loadHive($qualifiedName)
    {
        try {
            $path = str_replace('\\', '/', $qualifiedName);
            // Retrieve the contents from the registry file.
            // A shared lock is used to ensure another process
            // is not currently writing content to the registry.
            $this->getLock();
            $content = file_get_contents($this->archive[ $path ]);
            $this->releaseLock();
            // Unserialize the hive, if it updates itself
            // then update it in the registry also.
            /* @var $hive DRegistryHive */
            $hive = DCacheHandler::unserialize($content);
            $hive->initialise($this);
            if ($hive->isUpdated()) {
                $this->archive[ $path ] = serialize($hive);
            }
            // Catching a BadMethodCallException seems to be the only way
            // to check if a file actually exists in the archive, isset
            // doesn't work. A DSerializationException could also be thrown
            // if the registry has become corrupt, which means it will need
            // to be rebuilt.
        } catch (Exception $exception) {
            $hive = new $qualifiedName($this);
            $this->setHive($hive);
        }

        return $hive;
    }

    /**
     * Stores the provided hive within the registry.
     *
     * @param DRegistryHive $hive The hive to store.
     *
     * @return bool
     */
    public function setHive(DPharHive $hive)
    {
        if (!$hive instanceof DRegistryHive) {
            throw new DInvalidParameterValueException(
                'hive',
                array(__CLASS__, __FUNCTION__),
                'An object extending app\\decibel\\configuration\\DConfiguration'
            );
        }
        parent::setHive($hive);
        // Trigger the update event within the hive.
        $hive->triggerUpdate($this);

        return true;
    }
}
