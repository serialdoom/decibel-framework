<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\application;

use app\decibel\adapter\DAdaptable;
use app\decibel\adapter\DAdapterCache;
use app\decibel\application\DAppManifest;
use app\decibel\decorator\DDecoratable;
use app\decibel\decorator\DDecoratorCache;
use app\decibel\packaging\DAppDependency;
use app\decibel\regional\DGlobalTranslationScope;
use app\decibel\regional\DTranslationProvider;
use app\decibel\registry\DClassInfo;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DResult;

/**
 * Provides base functions for the registration of Apps.
 *
 * @author     Timothy de Paris
 * @ingroup    apps
 */
abstract class DApp implements DAdaptable, DDecoratable, DTranslationProvider
{
    use DBaseClass;
    use DAdapterCache;
    use DDecoratorCache;

    /**
     * The manifest for this App.
     *
     * @var        DAppManifest
     */
    protected $manifest;
    /**
     * Name of the App, as loaded from the App Manifest.
     *
     * @var        string
     */
    private $name;
    /**
     * Current version of the App, as loaded from the App Manifest.
     *
     * @var        string
     */
    private $version;
    /**
     * Copyright information for the App, as loaded from the App Manifest.
     *
     * @var        string
     */
    private $copyright;
    /**
     * Qualified name of this App.
     *
     * @var        string
     */
    protected $qualifiedName;

    /** @var string|null */
    private $relativePath;

    /**
     * Creates a new DApp instance.
     *
     */
    public function __construct()
    {
        $this->qualifiedName = get_class($this);
    }

    /**
     * Specifies whether installer packages can be generated through
     * the Decibel interface for this App.
     *
     * @return    DResult
     */
    public function canGenerateInstaller()
    {
        return new DResult('Installer', 'generated');
    }

    /**
     * Returns the location of this App on the file system.
     *
     * @return    string
     */
    final public function getAbsolutePath()
    {
        return DECIBEL_PATH . $this->getRelativePath();
    }

    /**
     * Returns the copyright message for this App.
     *
     * @return    string
     */
    public function getCopyright()
    {
        if ($this->copyright === null) {
            $manifest = $this->getManifest();
            $this->copyright = $manifest->getCopyright();
        }

        return $this->copyright;
    }

    /**
     * Returns a list of installed Apps that have a dependency on this App.
     *
     * @return    array    List of {@link DApp} objects.
     */
    public function getDependentApps()
    {
        $dependentApps = array();
        $appManager = DAppManager::load();
        foreach ($appManager->getApps() as $qualifiedName => $app) {
            /* @var $app DApp */
            $dependencies = $app->getManifest()
                                ->getDependencies(DAppDependency::class);
            foreach ($dependencies as $dependency) {
                /* @var $dependency DAppDependency */
                if ($dependency->getName() === $this->qualifiedName) {
                    $dependentApps[] = $app;
                }
            }
        }

        return $dependentApps;
    }

    /**
     * Returns a number between 0 and 9 showing the load priority of this App.
     *
     * Lower numbers have higher priority and will be loaded first. No custom
     * App should ever have a priority lower than 1, as some core features
     * may be unavailable to the App if loaded at this time.
     *
     * The default load priority is 5. This method should be overridden if a
     * higher or lower load order is required.
     *
     * @return    int
     */
    public function getLoadPriority()
    {
        return 5;
    }

    /**
     * Returns the manifest for this App.
     *
     * @return    DAppManifest
     */
    public function getManifest()
    {
        if ($this->manifest === null) {
            $classInfo = new DClassInfo(get_class($this));
            $this->manifest = new DAppManifest($this->getQualifiedName(),
                                               $this->getRelativePath() . $classInfo->className
                                                                        . '.manifest.xml');
        }

        return $this->manifest;
    }

    /**
     * Returns the name of the App.
     *
     * @return    string
     */
    public function getName()
    {
        if ($this->name === null) {
            $manifest = $this->getManifest();
            $this->name = $manifest->getName();
        }

        return $this->name;
    }

    /**
     * Returns the qualified class name of the App.
     *
     * @return    string
     */
    public function getQualifiedName()
    {
        return $this->qualifiedName;
    }

    /**
     * Returns the relative location of this App on the file system.
     *
     * @return string
     */
    final public function getRelativePath()
    {
        if ($this->relativePath === null) {
            $this->relativePath = dirname(str_replace(NAMESPACE_SEPARATOR,
                                                      DIRECTORY_SEPARATOR, $this->qualifiedName))
                                . DIRECTORY_SEPARATOR;
        }

        return $this->relativePath;
    }

    /**
     * @param string $relativePath
     *
     * @return DApp
     */
    public function setRelativePath($relativePath)
    {
        $new = clone $this;
        $new->relativePath = $relativePath;
        return $new;
    }

    /**
     * Returns the root namespace for this App.
     *
     * @section Example
     *
     * @code
     * use app\MyApp\MyApp;
     *
     * $app = new MyApp();
     * echo $app->getRootNamespace();
     * @endcode
     *
     * Will output:
     *
     * <em>app\\MyApp</em>
     *
     * @return    string
     */
    final public function getRootNamespace()
    {
        $parts = explode('\\', $this->qualifiedName);
        array_pop($parts);

        return implode('\\', $parts);
    }

    /**
     * Returns a list of provided translation files.
     *
     * @return    array    List of {@link DTranslationFile} objects.
     */
    public function getTranslationFiles()
    {
        $decorator = DAppTranslationFileLocator::decorate($this);
        return $decorator->getTranslationFiles();
    }

    /**
     * Returns the scope of the provided translations.
     *
     * @return    string    Qualified name of a class implementing {@link DTranslationScope}
     */
    public function getTranslationScope()
    {
        return DGlobalTranslationScope::class;
    }

    /**
     * Returns the current version of the App.
     *
     * @return    string
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $manifest = $this->getManifest();
            $this->version = $manifest->getVersion();
        }

        return $this->version;
    }
}
