<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\regional;

use app\decibel\application\DAppManager;
use app\decibel\cache\DPublicCache;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\utility\DBaseClass;

/**
 * Loads and stores available labels for a language.
 *
 * @author    Timothy de Paris
 */
class DLabelRepository
{
    use DBaseClass;

    /**
     * Language registration type.
     *
     * @var        string
     */
    const REGISTRATION_LABEL = 'label';

    /**
     * Loaded label repositories.
     *
     * @var        array
     */
    protected static $repositories = array();

    /**
     * The labels stored in this repository.
     *
     * @var        array
     */
    protected $labels;

    /**
     * The language of this repository.
     *
     * @var        string
     */
    protected $languageCode;

    /**
     * Loads a language repository.
     *
     * @param    string $languageCode The language for which the repository will be loaded.
     *
     * @return    static
     */
    public static function load($languageCode)
    {
        if (!isset(self::$repositories[ $languageCode ])) {
            self::$repositories[ $languageCode ] = new static($languageCode);
        }

        return self::$repositories[ $languageCode ];
    }

    /**
     * Loads a language repository.
     *
     * @param    string $languageCode The language for which the repository will be loaded.
     *
     * @return    static
     */
    protected function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
        // Load the labels from the cache if possible, otherwise from files.
        $publicCache = DPublicCache::load();
        $this->labels = $publicCache->retrieve(self::class, $languageCode);
        if ($this->labels === null) {
            $this->labels = self::loadLanguageFiles($languageCode);
            self::mergeLabelRegistrations($languageCode, $this->labels);
            // Cache for the next time.
            $publicCache->set(self::class, $languageCode, $this->labels);
        }
    }

    /**
     * Clears cached label information.
     *
     * @return    void
     */
    public static function clearCache()
    {
        self::$repositories = array();
        $publicCache = DPublicCache::load();
        // Clear registrations, as labels can be registered in info files.
        $publicCache->remove(DAppManager::class, DAppManager::CACHEKEY_REGISTRATIONS);
        // Clear generated label lists in all available languages.
        foreach (DLanguage::getLanguageCodes() as $languageCode) {
            $publicCache->remove(self::class, $languageCode);
        }
    }

    /**
     * Returns a label from the repository.
     *
     * @note
     * If this repository does not contain the label, and it is not the repository
     * for the default application language (<code>DECIBEL_REGIONAL_DEFAULTLANGUAGE</code>),
     * this method will attempt to load the label from the default application
     * language repository.
     *
     * @param    string $name      Label name.
     * @param    string $namespace Label namespace.
     * @param    string $scope     Qualified name of the translation scope.
     *
     * @return    string    The label, or <code>null</code> if no label was found.
     */
    public function getLabel($name, $namespace, $scope = DGlobalTranslationScope::class)
    {
        if (isset($this->labels[ $scope ][ $namespace ][ $name ])) {
            $label = $this->labels[ $scope ][ $namespace ][ $name ];
        } else {
            if ($scope !== DGlobalTranslationScope::class
                && isset($this->labels[ DGlobalTranslationScope::class ][ $namespace ][ $name ])
            ) {
                $label = $this->labels[ DGlobalTranslationScope::class ][ $namespace ][ $name ];
            } else {
                // todo: move this out to a dedicated ConfigurationManager
                $locale = env('LANG', env('LC_ALL', 'en_GB.UTF-8'));
                $language = substr($locale, 0, strpos($locale, '.'));

                if ($this->languageCode !== $language) {
                    $repository = static::load($language);
                    $label = $repository->getLabel($name, $namespace, $scope);
                } else {
                    $label = null;
                }
            }
        }

        return $label;
    }

    /**
     * Returns the language of the labels this repository contains.
     *
     * @return    string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Determines if any repositories are currently loaded.
     *
     * @return    bool
     */
    public static function isLoaded()
    {
        return (bool)self::$repositories;
    }

    /**
     * Load language files using the language code.
     *
     * @param    string $languageCode Language for which to load labels.
     *
     * @return    array
     */
    protected static function loadLanguageFiles($languageCode)
    {
        $labels = array(
            DGlobalTranslationScope::class => array(),
        );
        // Enumerate through all Apps.
        $registry = DGlobalRegistry::load();
        $translationsHive = $registry->getHive(DTranslationFileInformation::class);
        $translationFiles = $translationsHive->getTranslationFiles($languageCode);
        foreach ($translationFiles as $translationFile) {
            /* @var $translationFile DTranslationFile */
            self::mergeLabels(
                $labels[ DGlobalTranslationScope::class ],
                $translationFile->getLabels()
            );
        }

        return $labels;
    }

    /**
     * Merges labels registered by other Apps.
     *
     * @param    string $languageCode Language for which to load labels.
     * @param    array  $labels       Existing labels in which to merge registered labels.
     *
     * @return    void
     */
    protected static function mergeLabelRegistrations($languageCode, array &$labels)
    {
        $registrations = DAppManager::getRegistration(
            self::class,
            self::REGISTRATION_LABEL,
            $languageCode
        );
        if ($registrations) {
            foreach ($labels as &$level) {
                // Merge the labels into the existing namespace.
                // Labels with the same name will overwrite existing
                // labels loaded from the Apps.
                self::mergeLabels(
                    $level,
                    $registrations
                );
            }
        }
    }

    /**
     * Merges a namespaced list of new labels into an existing
     * namespaced list of labels.
     *
     * @param    array $labels    Pointer to the list of labels.
     * @param    array $newLabels New labels to merge into the existing list.
     *
     * @return    void
     */
    protected static function mergeLabels(array &$labels, array $newLabels)
    {
        foreach ($newLabels as $namespace => &$namespaceLabels) {
            // Initialise the namespace if it doesn't yet exist,
            // otherwise array_merge will return null.
            if (!isset($labels[ $namespace ])) {
                $labels[ $namespace ] = array();
            }
            $labels[ $namespace ] = array_merge(
                $labels[ $namespace ],
                $namespaceLabels
            );
        }
    }

    /**
     * Registers labels for a language.
     *
     * @param    string $languageCode Language code.
     * @param    mixed  $values       Values.
     *
     * @return    void
     */
    public static function registerLabels($languageCode, $values)
    {
        DAppManager::addRegistration(
            self::class,
            self::REGISTRATION_LABEL,
            $values,
            $languageCode
        );
    }
}
