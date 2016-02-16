<?php
namespace app\decibel\configuration;

use app\decibel\utility\DSingleton;

/**
 * Interface DConfigurationStoreInterface
 *
 * Provides a shared interface for ConfigurationStores
 *
 * @package configuration
 */
interface DConfigurationStoreInterface extends DSingleton
{
    /**
     * @param string $qualifiedName
     *
     * @return DConfiguration
     */
    public function getHive($qualifiedName);
}
