<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\application;

use app\decibel\application\debug\DMissingAppManifestDataException;
use app\decibel\application\debug\DMissingAppManifestException;
use app\decibel\file\DFile;
use app\decibel\file\DFileNotFoundException;
use app\decibel\health\DHealthCheckResult;
use app\decibel\packaging\DAppDependency;
use app\decibel\packaging\DDependency;
use app\decibel\xml\DDOMDocument;
use app\decibel\xml\DXPath;
use DOMElement;

/**
 * Provides a wrapper for accessing an App's manifest file.
 *
 * @author    Timothy de Paris
 */
class DAppManifest extends DXPath
{
    /**
     * 'Version' XML element name.
     *
     * @var        string
     */
    const ELEMENT_VERSION = 'version';
    /**
     * 'Name' XML element name.
     *
     * @var        string
     */
    const ELEMENT_NAME = 'name';

    /**
     * The filename for this manifest.
     *
     * @var        string
     */
    protected $filename;

    /**
     * Loads an App manifest from the provided filename.
     *
     * @param    string $qualifiedName    Qualified name of the App to load
     *                                    the manifest for.
     * @param    string $filename         Optional location of the manifest file.
     *                                    If not provided it will be looked for
     *                                    in the expected location for the
     *                                    provided App.
     *
     * @throws DMissingAppManifestException If the specified manifest file
     *                                            does not exist.
     */
    public function __construct($qualifiedName, $filename = null)
    {
        // Determine the expected location for the App
        // if no filename provided.
        if ($filename === null) {
            $this->filename = static::getManifestFilename($qualifiedName);
        } else {
            $this->filename = $filename;
        }

        try {
            $file = new DFile($this->filename);
            // Handle exception if manifest file doesn't exist.
        } catch (DFileNotFoundException $exception) {
            throw new DMissingAppManifestException($this->filename, $qualifiedName);
        }

        $stream = $file->getStream();

        $document = DDOMDocument::create($stream);
        parent::__construct($document);
    }

    /**
     * Checks the dependencies of the App manifest against the current
     * installation.
     *
     * @param    array $versions      Optional array of current state overrides
     *                                for DAppDependency object that can be provided
     *                                to test dependencies in    particular situations.
     *
     * @return    array    List of {@link DHealthCheckResult} objects.
     */
    public function checkDependencies(array $versions = array())
    {
        // Find dependencies within the manifest.
        $results = array();
        foreach ($this->getDependencies() as $dependency) {
            /* @var $dependency DDependency */
            $result = $this->checkDependency($dependency, $versions);
            if ($result !== null) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Checks a dependency against the current installation.
     *
     * @param    DDependency $dependency  The dependency to check.
     * @param    array       $versions    Optional array of current state overrides
     *                                    for DAppDependency object that can be provided
     *                                    to test dependencies in    particular situations.
     *
     * @return    DHealthCheckResult    A warning or error, or <code>null</code>
     *                                if the dependency is satisfied.
     */
    protected function checkDependency(DDependency $dependency, array $versions)
    {
        // Override the current state if required.
        if ($dependency instanceof DAppDependency
            && isset($versions[ $dependency->getName() ])
        ) {
            $dependency->setCurrentState($versions[ $dependency->getName() ]);
        }
        // Test the dependencies.
        $test = $dependency->test();
        if ($test->hasMessages()) {
            $messages = $test->getMessages();
            $currentState = $dependency->getCurrentState();
            if ($currentState) {
                $message = "{$messages[0]} Current environment: {$currentState}";
            } else {
                $message = $messages[0];
            }
        }
        if (!$test->isSuccessful()) {
            $result = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_ERROR,
                $message
            );
        } else {
            if ($test->hasMessages()) {
                $result = new DHealthCheckResult(
                    DHealthCheckResult::HEALTH_CHECK_WARNING,
                    $message
                );
            } else {
                $result = null;
            }
        }

        return $result;
    }

    /**
     * Returns the name of the author of the App represented by this manifest.
     *
     * This function will return the name of the first author listed
     * in the manifest.
     *
     * @return    string    The author's name, or null if there is no author
     *                    information in the manifest.
     */
    public function getAuthorName()
    {
        $query = $this->query('//manifest/authors/author/name');
        if ($query->length) {
            $authorName = $query->item(0)->textContent;
        } else {
            $authorName = null;
        }

        return $authorName;
    }

    /**
     * Returns copyright information for the App represented by this manifest.
     *
     * @return    string    The copyright information, or null if there is no
     *                    copyright information in the manifest.
     */
    public function getCopyright()
    {
        $query = $this->query('//manifest/copyright');
        if ($query->length) {
            $copyright = $query->item(0)->textContent;
        } else {
            $copyright = null;
        }

        return $copyright;
    }

    /**
     * Returns the dependencies defined by this manifest.
     *
     * @param    string $type     If specified, only dependencies matching this qualified
     *                            name will be returned.
     *
     * @return    array    List of {@link app::decibel::packaging::DDependency DDependency}
     *                    objects.
     */
    public function getDependencies($type = null)
    {
        $queryPath = '//manifest/dependencies/dependency';
        if ($type !== null) {
            $queryPath .= "[@type='{$type}']";
        }
        // Find dependencies within the manifest.
        $dependencies = array();
        foreach ($this->query($queryPath) as $dependency) {
            /* @var $dependency DOMElement */
            $dependencies[] = DDependency::createFromXml($dependency);
        }

        return $dependencies;
    }

    /**
     * Returns the name of the file from which this manifest was loaded.
     *
     * @return    string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Determines the location of the manifest file for a specified App.
     *
     * @note
     * This method does not validate the provided qualified name.
     *
     * @param    string $qualifiedName    Qualified name of the App to locate
     *                                    the manifest file for.
     *
     * @return    string
     */
    protected static function getManifestFilename($qualifiedName)
    {
        $path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $qualifiedName);
        return (DECIBEL_PATH . $path . '.manifest.xml');
    }

    /**
     * Returns the name of the App represented by this manifest.
     *
     * @return    string
     * @throws    DMissingAppManifestDataException    If the name is missing
     *                                                from the manifest.
     */
    public function getName()
    {
        $query = $this->query('//manifest/name');
        if ($query->length) {
            return $query->item(0)->textContent;
        }
        throw new DMissingAppManifestDataException(
            $this->filename,
            self::ELEMENT_NAME
        );
    }

    /**
     * Returns the URL for the repository that contains updates for the App
     * represented by this manifest.
     *
     * @return    string    The repository URL, or null if there is no respority
     *                    information in the manifest.
     */
    public function getRepositoryUrl()
    {
        $query = $this->query('//manifest/respository/location');
        if ($query->length) {
            $url = $query->item(0)->textContent;
        } else {
            $url = null;
        }

        return $url;
    }

    /**
     * Returns the update method supported by the App represented
     * by this manifest.
     *
     * @return    string    The update method, or 'manual' if there is no
     *                    update method in the manifest.
     */
    public function getUpdateMethod()
    {
        $query = $this->query('//manifest/respository[@method]');
        if ($query->length) {
            $method = $query->item(0)->getAttribute('method');
        } else {
            $method = 'manual';
        }

        return $method;
    }

    /**
     * Returns the version for the App represented by this manifest.
     *
     * @return    string
     * @throws    DMissingAppManifestDataException    If the version is missing
     *                                                from the manifest.
     */
    public function getVersion()
    {
        $query = $this->query('//manifest/version');
        if ($query->length) {
            return $query->item(0)->textContent;
        }
        throw new DMissingAppManifestDataException(
            $this->filename,
            self::ELEMENT_VERSION
        );
    }
}
