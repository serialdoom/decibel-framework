<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional\language;

use app\decibel\registry\DClassInformation;
use app\decibel\registry\DRegistryHive;

/**
 * Registers information about languages known by Decibel.
 *
 * @author    Timothy de Paris
 */
class DLanguageInformation extends DRegistryHive
{
    /**
     * Index of language definitions within the scope of the registry.
     *
     * @var        array
     */
    protected $languages = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['languages'] = $this->languages;

        return $debug;
    }

    ///@cond INTERNAL
    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'languages';

        return $sleep;
    }
    ///@endcond
    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $classInformation DClassInformation */
        $classInformation = $this->getDependency(DClassInformation::class);

        return $classInformation->getChecksum();
    }

    /**
     * Returns the language definition
     *
     * Returns the appropriate language definition for the provided
     * language code.
     *
     * @param    string $languageCode The ISO 639-1 two-letter language code
     *
     * @return    DLanguageDefinition
     * @throws    DMissingLanguageDefinitionException    If no definition is available
     *                                                for this language code.
     */
    public function getLanguageDefinition($languageCode)
    {
        if (!array_key_exists($languageCode, $this->languages)) {
            throw new DMissingLanguageDefinitionException($languageCode);
        }
        $qualifiedName = $this->languages[ $languageCode ];

        return new $qualifiedName();
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(DClassInformation::class);
    }

    /**
     * Returns a version number indicating the format of the registry.
     *
     * @return    int
     */
    public function getFormatVersion()
    {
        return 1;
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool
     */
    public function merge(DRegistryHive $hive)
    {
        if (!$hive instanceof DLanguageInformation) {
            return false;
        }
        $this->languages = array_merge(
            $this->languages,
            $hive->languages
        );

        return true;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    protected function rebuild()
    {
        $this->languages = array();
        /* @var $classInformation DClassInformation */
        $classInformation = $this->getDependency(DClassInformation::class);
        $languageDefinitions = $classInformation->getClassNames(DLanguageDefinition::class);
        foreach ($languageDefinitions as $qualifiedName) {
            /* @var $definition DLanguageDefinition */
            $definition = new $qualifiedName();
            $this->languages[ $definition->getLanguageCode() ] = $qualifiedName;
        }
    }
}
