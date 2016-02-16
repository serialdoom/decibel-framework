<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\configuration;

use app\decibel\cache\DCacheHandler;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\debug\DNotImplementedException;
use app\decibel\utility\DPharHive;
use app\decibel\utility\DPharRepository;
use Exception;

/**
 * Provides a repository in which {@link DConfiguration} objects can be stored.
 *
 * @author        Timothy de Paris
 */
abstract class DConfigurationStore extends DPharRepository implements DConfigurationStoreInterface
{
    /**
     * Attempts to load a hive from the Phar archive.
     *
     * @param    string $qualifiedName Qualified name of the configuration to retrieve.
     *
     * @return    DConfiguration
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
            // Unserialize the hive.
            $hive = DCacheHandler::unserialize($content);
            // Catching a BadMethodCallException seems to be the only way
            // to check if a file actually exists in the archive, isset
            // doesn't work. A DSerializationException could also be thrown
            // if the repository has become corrupt, which means it will need
            // to be rebuilt.
        } catch (Exception $exception) {
            // @todo Decide how to handle these exceptions.
            $hive = null;
        }

        return $hive;
    }

    /**
     * Stores the provided hive within the repository.
     *
     * @param    DConfiguration $hive The hive to store.
     *
     * @return    bool
     * @throws    DInvalidParameterValueException    If the provided hive is invalid.
     */
    public function setHive(DPharHive $hive)
    {
        if (!$hive instanceof DConfiguration) {
            throw new DInvalidParameterValueException(
                'hive',
                array(__CLASS__, __FUNCTION__),
                'An object extending app\\decibel\\configuration\\DConfiguration'
            );
        }

        return parent::setHive($hive);
    }
}
