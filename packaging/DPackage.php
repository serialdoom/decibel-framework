<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

use app\decibel\application\DAppManifest;
use app\decibel\application\debug\DMissingAppManifestException;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DInvalidPropertyException;
use app\decibel\file\DFileSystemException;
use app\decibel\file\DFileSystemFilter;
use app\decibel\file\DLocalFileSystem;
use app\decibel\file\DRecursiveFileIterator;
use app\decibel\packaging\DInvalidPackageException;
use app\decibel\packaging\DManifest;
use BadMethodCallException;
use Phar;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * Provide the ability to create and extract packages.
 *
 * @author    Timothy de Paris
 */
class DPackage extends Phar
{
    /**
     * The name of the file in which this package is stored.
     *
     * @var        string
     */
    protected $filename;
    /**
     * The manifest for this package.
     *
     * @var        DManifest
     */
    protected $manifest;

    /**
     * Loads a package from a file.
     *
     * @param    string $filename The name of the package file.
     *
     * @throws    DInvalidPackageException    If the specified file
     *                                        is not a valid package.
     * @return    static
     */
    public function __construct($filename)
    {
        if (!file_exists($filename)) {
            throw new DInvalidPackageException($filename, 'The specified file does not exist.');
        }
        $file = substr($filename, strrpos($filename, '/') + 1);
        try {
            parent::__construct($filename);
        } catch (UnexpectedValueException $e) {
            throw new DInvalidPackageException($file, 'This is not a valid Decibel package (invalid PHAR package).');
        }
        // Check that all expected meta data is present.
        if (!$this->hasMetadata()) {
            throw new DInvalidPackageException($file, 'This is not a valid Decibel package (missing metadata).');
        }
        // Try to load the manifest.
        try {
            $this->manifest = DManifest::fromArray($this->getMetadata());
        } catch (DInvalidPropertyException $e) {
            throw new DInvalidPackageException($file, 'This is not a valid Decibel package (invalid manifest).');
        }
        $this->filename = $filename;
    }

    /**
     * Determines if the current installation can read packages.
     *
     * @return    bool
     */
    public static function canReadPackages()
    {
        return true;
    }

    /**
     * Extracts the contents of the Package to a specified location.
     *
     * @note
     * Overrides the Phar::extractTo() function to add checksum validation
     * and avoid PHP Bug #50797 (https://bugs.php.net/bug.php?id=50797)
     *
     * @param    string $pathto    The location to extract the package.
     * @param    array  $files     Not currently implemented
     * @param    bool   $overwrite Whether to overwrite existing files.
     *
     * @return    bool
     * @throws    DInvalidPackageException    If the package is unable to be
     *                                        extracted due to a failed checksum.
     */
    public function extractTo($pathto, $files = null, $overwrite = false)
    {
        foreach (new RecursiveIteratorIterator($this) as $file) {
            // Ignore directories.
            if ($file->isDir()) {
                continue;
            }
            try {
                $this->extractFile($pathto, $file, $overwrite);
                // If this fails, delete any extracted
                // contents so far and throw the exception.
            } catch (DInvalidPackageException $exception) {
                $fileSystem = new DLocalFileSystem();
                $fileSystem->deltree($pathto);
                throw $exception;
            }
        }

        return true;
    }

    /**
     * Extracts a file from the package.
     *
     * @param    string      $pathto    The path to extract the file to.
     * @param    SplFileInfo $file      The file to extract.
     * @param    bool        $overwrite Whether to overwrite existing files.
     *
     * @throws    DInvalidPackageException    If the extracted file fails
     *                                        a checksum.
     * @return    void
     */
    protected function extractFile($pathto, SplFileInfo $file,
                                   $overwrite = false)
    {
        // Create the folder in which the file will be saved, if neccessary.
        $pharPath = "phar://{$this->filename}/";
        $dirFrom = $file->getPath() . '/';
        $dirTo = str_replace($pharPath, $pathto, $dirFrom);
        if (!is_dir($dirTo)) {
            $fileSystem = new DLocalFileSystem();
            $fileSystem->mkdir($dirTo);
        }
        // Determine extracted file name.
        $from = $dirFrom . $file->getFilename();
        $to = $dirTo . $file->getFilename();
        // Ignore existing files, or delete if we are overwriting.
        if (file_exists($to)) {
            if ($overwrite) {
                unlink($to);
            } else {
                return;
            }
        }
        // Write the file.
        file_put_contents($to, file_get_contents($from));
        // Validate the checksum.
        $basename = str_replace($pharPath, '', $file->getPathname());
        if ($this->manifest->validateChecksum($basename, $to) === false) {
            throw new DInvalidPackageException($this->filename,
                                               "Checksum validation failed for file {$basename}. Unable to extract package contents.");
        }
    }

    /**
     * Returns a manifest for the App with the specified qualified name.
     *
     * @param    string $qualifiedName Qualified name of the App.
     *
     * @return    DAppManifest    The App manifest, or <code>null</code> if the
     *                            requested App is not present in the package.
     */
    public function getAppManifest($qualifiedName)
    {
        // Load the App manifest.
        $path = str_replace('\\', '/', $qualifiedName);
        $filename = dirname($path)
            . '/' . basename($path)
            . '.manifest.xml';
        try {
            $manifest = new DAppManifest($qualifiedName, $this[ $filename ]);
        } catch (BadMethodCallException $exception) {
            $manifest = null;
        } catch (DMissingAppManifestException $exception) {
            $manifest = null;
        }

        return $manifest;
    }

    /**
     * Returns a human-readable list of the package contents.
     *
     * @return    array
     */
    public function getContents()
    {
        $contents = array();
        foreach ($this->manifest->getApps() as $app) {
            $appManifest = $this->getAppManifest($app);
            $contents[] = $appManifest->getName();
        }

        return $contents;
    }

    /**
     * Returns the name of the file in which this package is stored.
     *
     * @return    string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the size of this package file on the disk, in bytes.
     *
     * @return    int
     */
    public function getFileSize()
    {
        return filesize($this->filename);
    }

    /**
     * Returns the manifest for this package.
     *
     * @return    DManifest
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Retrieves a list of packages currently stored by this Decibel installation.
     *
     * @return    array    List of {@link DPackage} objects with file names as keys.
     */
    public static function getPackages()
    {
        $packages = array();
        try {
            $iterator = DRecursiveFileIterator::getIterator(PACKAGE_PATH);
        } catch (DFileSystemException $e) {
            return $packages;
        }
        foreach ($iterator as $packageFile) {
            /* @var $packageFile SplFileInfo */
            // Load the package and check it is valid.
            try {
                $absolutePath = $packageFile->getPathname();
                $package = new DPackage($absolutePath);
                $pathname = $package->getFilename();
                $filename = str_replace(PACKAGE_PATH, '', $pathname);
                $packages[ $filename ] = $package;
            } catch (DInvalidPackageException $e) {
                DErrorHandler::logException($exception);
            }
        }

        return $packages;
    }

    /**
     * Compares packages to allow ordering.
     *
     * @param    DPackage $a
     * @param    DPackage $b
     *
     * @return    int
     */
    public static function comparePackages(DPackage $a, DPackage $b)
    {
        $createdA = $a->getManifest()
                      ->getCreatedTime();
        $createdB = $b->getManifest()
                      ->getCreatedTime();
        if ($createdA > $createdB) {
            $compare = -1;
        } else {
            if ($createdA < $createdB) {
                $compare = 1;
            } else {
                $compare = 0;
            }
        }

        return $compare;
    }

    /**
     * Creates a list of all files in the specified directories that will
     * be included in a package.
     *
     * @param    array $directories The directories to index.
     *
     * @return    array
     */
    public static function index(array $directories)
    {
        $files = array();
        $pathLength = strlen(DECIBEL_PATH);
        // Ignore dot files and temporary files (starting with '%')
        $filter = DFileSystemFilter::create()
                                   ->setRegex('/^[^\.%]/');
        while (count($directories)) {
            $directory = array_pop($directories);
            $iterator = DRecursiveFileIterator::getIterator($directory, $filter);
            foreach ($iterator as $file) {
                /* @var $file SplFileInfo */
                $pathname = $file->getPathname();
                $basename = substr($pathname, $pathLength);
                $files[ $basename ] = $pathname;
            }
        }

        return $files;
    }
}
