<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

use app\decibel\debug\DErrorHandler;
use app\decibel\file\DFileExistsException;
use app\decibel\file\DLocalFileSystem;
use app\decibel\packaging\DInvalidPackageException;
use app\decibel\packaging\DInvalidRepositoryException;
use app\decibel\packaging\DPackage;
use app\decibel\rpc\DCurlRequest;
use app\decibel\rpc\debug\DInvalidRemoteProcedureCallException;
use DOMDocument;
use DOMXPath;

/**
 * Provide the ability to read and write update repositories.
 *
 * @author    Timothy de Paris
 */
class DRepository
{
    /**
     * Decibel "release" update stream.
     *
     * @var        string
     */
    const STREAM_RELEASE = 'release';
    /**
     * Decibel "edge" update stream.
     *
     * @var        string
     */
    const STREAM_EDGE = 'edge';
    /**
     * Decibel "stable" update stream.
     *
     * @var        string
     */
    const STREAM_STABLE = 'stable';
    /**
     * The XPath object for this repository.
     *
     * @var        DOMXPath
     */
    public $repository;

    /**
     * Loads a repository from a file.
     *
     * @param    $xml    The repository XML content.
     *
     * @throws    DInvalidRepositoryException    If this is not a valid repsository.
     * @return    DRespository
     */
    protected function __construct($xml)
    {
        // Work around for loadXML function not using exceptions...
        DErrorHandler::silentMode(true);
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xml);
        $this->repository = new DOMXPath($domDocument);
        DErrorHandler::silentMode(false);
    }

    /**
     * Loads a repository from the provided URL.
     *
     * @param    string $url URL of the repository.
     *
     * @return    static
     * @throws    DInvalidRepositoryException    If this is not a valid repsository.
     */
    public static function loadFromUrl($url)
    {
        try {
            $xml = DCurlRequest::create($url)
                               ->execute();
            // If the RPC call fails, convert the exception
            // to a DInvalidRepositoryException.
        } catch (DInvalidRemoteProcedureCallException $exception) {
            throw new DInvalidRepositoryException($url, $exception->getMessage());
        }

        return new static($xml);
    }

    /**
     * Downloads a package that can be used to update the specified
     * App from it's current version.
     *
     * @note
     * If more than one matching record is found in the repository,
     * the first record will be downloaded.
     *
     * @param    string $qualifiedName    Qualified name of the App.
     * @param    string $currentVersion   Current version of the App to download
     *                                    only a more recent package, or <code>null</code>
     *                                    to download the most recent package.
     * @param    string $stream           The update stream to query.
     * @param    string $filename         If provided, the package will be saved
     *                                    using the provided filename. If not,
     *                                    the package will be saved in the default
     *                                    location.
     *
     * @return    DPackage    The downloaded package, or <code>null</code>
     *                        if no package was matched.
     * @throws    DFileExistsException    If a package already exists at the
     *                                    location this package is to be downloaded.
     * @throws    DInvalidPackageException    If the downloaded package is invalid.
     */
    public function downloadPackage($qualifiedName, $currentVersion = null,
                                    $stream = self::STREAM_STABLE, $filename = null)
    {
        // Check the repository for available packages.
        $version = null;
        $packageLocation = $this->getPackageLocation(
            $qualifiedName,
            $currentVersion,
            $stream,
            $version
        );
        if ($packageLocation === null) {
            return null;
        }
        // Determine the default name for the package if none specified.
        if ($filename === null) {
            $filename = PACKAGE_PATH
                . str_replace('\\', '-', $qualifiedName)
                . '_' . $version . '_install.phar';
        }
        // Check if a file already exists here.
        if (file_exists($filename)) {
            throw new DFileExistsException($filename);
        }
        // The download might take a while...
        set_time_limit(0);
        // Download the package.
        $handle = curl_init($packageLocation);
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        // Execute the remote procedure and store the package.
        $packageContents = curl_exec($handle);
        // Create the package folder if it doesn't exist.
        $fileSystem = new DLocalFileSystem();
        $directory = dirname($filename);
        if (!is_dir($directory)) {
            $fileSystem->mkdir($directory);
        }
        // Write the package file.
        file_put_contents($filename, $packageContents);
        curl_close($handle);
        // Load the package and return.
        try {
            return new DPackage($filename);
            // If the downloaded package is invalid,
            // delete it and throw the exception.
        } catch (DInvalidPackageException $exception) {
            unlink($filename);
            throw $exception;
        }
    }

    /**
     * Queries the repository for information about the latest available
     * package for the specified App.
     *
     * @note
     * If more than one matching record is found in the repository,
     * the first record will be returned.
     *
     * @param    string $qualifiedName Qualified name of the App to query.
     * @param    string $stream        The update stream to query.
     *
     * @return    DOMNodeList    The package details, or <code>null</code>
     *                        if no package was matched.
     */
    protected function getPackageDetails($qualifiedName, $stream = self::STREAM_STABLE)
    {
        // Attempt to locate a package in the specified stream first.
        $streamQuery = "//repository//package[@qualifiedName=\"{$qualifiedName}\" and @stream=\"{$stream}\"]";
        $streamResult = $this->repository->query($streamQuery);
        if ($streamResult->length) {
            $package = $streamResult->item(0);
            // Otherwise, look for a package without a stream.
        } else {
            $withoutStreamQuery = "//repository//package[@qualifiedName=\"{$qualifiedName}\" and not(@stream)]";
            $withoutStreamResult = $this->repository->query($withoutStreamQuery);
            if ($withoutStreamResult->length) {
                $package = $withoutStreamResult->item(0);
            } else {
                $package = null;
            }
        }

        return $package;
    }

    /**
     * Returns the location of a package that can be used to update
     * the specified App from it's current version.
     *
     * @note
     * If more than one matching record is found in the repository,
     * the first record will be returned.
     *
     * @param    string $qualifiedName    Qualified name of the App.
     * @param    string $currentVersion   Current version of the App to download
     *                                    only a more recent package, or <code>null</code>
     *                                    to download the most recent package.
     * @param    string $stream           The update stream to query.
     * @param    string $version          Pointer in which the version
     *                                    of the matched package will be returned.
     *
     * @return    DOMNodeList    The package location, or <code>null</code>
     *                        if no package was matched.
     */
    public function getPackageLocation($qualifiedName, $currentVersion = null,
                                       $stream = self::STREAM_STABLE, &$version = null)
    {
        // Check the repository for available packages.
        $packageDetails = $this->getPackageDetails($qualifiedName, $stream);
        if ($packageDetails === null) {
            $location = null;
        } else {
            // Test the version of the package.
            $version = $packageDetails->getAttribute('version');
            if ($currentVersion !== null
                && version_compare($version, $currentVersion, '<=')
            ) {
                $location = null;
                // Return the package location.
            } else {
                $location = $this->repository
                    ->query('location', $packageDetails)
                    ->item(0)->textContent;
            }
        }

        return $location;
    }
}
