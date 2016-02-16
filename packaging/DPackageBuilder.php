<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

use app\decibel\debug\DErrorHandler;
use app\decibel\server\DServer;
use app\decibel\utility\DResult;
use ArrayIterator;
use BadMethodCallException;
use Phar;
use PharException;
use UnexpectedValueException;

/**
 * Provides functionality to build a Decibel update package.
 *
 * @author    Timothy de Paris
 */
class DPackageBuilder
{
    /**
     * The package manifest.
     *
     * @var        DManifest
     */
    protected $manifest;
    /**
     * The package contents.
     *
     * @var        array
     */
    protected $contents;

    /**
     * Create a new {@link DPackageBuilder}.
     *
     * @param    DManifest $manifest      Description of the package contents.
     * @param    array     $contents      Files to add to the package.
     * @param    string    $filename      Location to save the package.
     * @param    string    $stub          The PHP code to add to the package stub.
     *                                    This must begin with the <code>&lt;?php</code> tag and
     *                                    finish with <code>__HALT_COMPILER();</code>
     *
     * @throws    DPackageGenerationException    If the package is unable
     *                                        to be created.
     * @return    static
     */
    public function __construct(DManifest $manifest,
                                array $contents, $filename, $stub = null)
    {
        // Check that packages can be written.
        if (!self::canWritePackages()) {
            throw new DPackageGenerationException('The current server configuration does not allow packages to be created.');
        }
        // Check if the package file already exists.
        if (file_exists($filename)) {
            throw new DPackageGenerationException('A file already exists at the specified location.');
        }
        $this->manifest = $manifest;
        $this->contents = $contents;
        $this->filename = $filename;
        $this->stub = $stub;
    }

    /**
     * Adds the provided package contents to the phar archive.
     *
     * @param    ArrayIterator $contents The package contents.
     * @param    Phar          $phar     The Phar archive.
     * @param    int           $size     Total file size of the package.
     *
     * @return    void
     * @throws    DPackageGenerationException    If a file was unable to be added.
     */
    protected function addContents(ArrayIterator $contents,
                                   Phar $phar, $size)
    {
        try {
            // Decide whether to use Phar::buildFromIterator or Phar::addFile
            // depending on whether the tmp folder has enough free space.
            // Phar::buildFromIterator is much, much faster but requires about
            // twice the amount of free space.
            if (disk_free_space(sys_get_temp_dir()) > ($size * 2)) {
                $phar->buildFromIterator($contents);

                return;
            }
            foreach ($contents as $localname => $file) {
                $phar->addFile($file, $localname);
            }
        } catch (UnexpectedValueException $e) {
            DErrorHandler::logException($e);
            throw new DPackageGenerationException('Attempted to add invalid file to package. Please see the error log for further details.');
        } catch (PharException $e) {
            DErrorHandler::logException($e);
            throw new DPackageGenerationException('Unable to generate the package. Please see the error log for further details.');
        }
    }

    /**
     * Builds the package.
     *
     * @return    DResult
     * @throws    DPackageGenerationException
     */
    public function build()
    {
        // Test the provided package contents.
        $size = 0;
        $contents = new ArrayIterator($this->contents);
        $result = new DResult('Package', 'created');
        $result->merge($this->testContents($contents, $this->manifest, $size));
        // Check if package creation can continue.
        if (!$result->isSuccessful()) {
            return $result;
        }
        // Create a new Phar archive.
        $phar = new Phar($this->filename, 0, basename($this->filename));
        $phar->setSignatureAlgorithm(Phar::MD5);
        // Add contents.
        $this->addContents($contents, $phar, $size);
        // Generate checksums for added files.
        $result->merge($this->manifest->addChecksums($contents, $phar));
        if (!$result->isSuccessful()) {
            unset($phar);
            unlink($this->filename);

            return $result;
        }
        // Add the manifest to the backup.
        $phar->setMetadata((array)$this->manifest->jsonPrepare());
        // Set the stub.
        try {
            $this->setPackageStub($phar, $this->stub);
            // Clean up if there is an error with the stub.
        } catch (DPackageGenerationException $exception) {
            unset($phar);
            unlink($this->filename);
            throw $exception;
        }
        // Attempt to compress that package.
        $this->compressPackage($phar, $result);
        // Validate package contents.
        foreach ($contents as $basename => $pathname) {
            if ($this->manifest->validateChecksum($basename, "phar://{$this->filename}/{$basename}") === false) {
                $result->setSuccess(false, "Invalid checksum for file <code>{$basename}</code>");
            }
        }

        return $result;
    }

    /**
     * Determines if the current installation can write packages.
     *
     * @return    bool
     */
    public static function canWritePackages()
    {
        return !((bool)ini_get('phar.readonly'));
    }

    /**
     * Attempts to compress the package.
     *
     * @note
     * This method provides workarounds for a number of PHP Phar related
     * bugs, including:
     * - https://bugs.php.net/bug.php?id=53467
     *
     * @param    Phar    $phar   The package.
     * @param    DResult $result Result of the packaging operation.
     *
     * @return    void
     */
    protected function compressPackage(Phar $phar, DResult $result)
    {
        // Another workaround for https://bugs.php.net/bug.php?id=53467
        // we need to ensure the DPackageCompressionException file is loaded
        // before this error could occur, otherwise there won't be any
        // file handles available to load it!
        new DPackageCompressionException();
        // Compress the package
        try {
            // Only bother compressing with less than OS open file limit,
            // to try and avoid creating the bug mentioned below.
            // If it is caused, checksum creation may fail due to
            // subsequent "writable file pointers are open" phar exception.
            $server = DServer::load();
            $openFileLimit = $server->getOpenFileLimit();
            $fileCount = count($phar);
            if ($fileCount <= $openFileLimit) {
                $phar->compressFiles(Phar::GZ);
                // If we know compression won't work, add a message to the result.
            } else {
                $result->addMessage("Package includes {$fileCount} files which exceeds OS file limit of {$openFileLimit}. Package unable to be compressed. See https://bugs.php.net/bug.php?id=53467 for further information.");
            }
            // This handles https://bugs.php.net/bug.php?id=53467
        } catch (BadMethodCallException $e) {
            $result->addMessage('Unable to compress package: ' . $e->getMessage());
            DErrorHandler::logException(new DPackageCompressionException($e->getMessage()));
        }
    }

    /**
     * Sets the stub for the provided Phar archive.
     *
     * @param    Phar   $phar The Phar archive.
     * @param    string $stub The stub.
     *
     * @return    void
     * @throws    DPackageGenerationException    If the stub is invalid.
     */
    protected function setPackageStub(Phar $phar, $stub = null)
    {
        // Load the default stub if none provided.
        if ($stub === null) {
            $stub = file_get_contents(__DIR__ . '/default.stub.php');
        }
        // Test the provided stub.
        if (!preg_match('/^\s*<\?php.*__HALT_COMPILER\(\);(\s+?\?>\s*)?$/Uus', $stub)) {
            throw new DPackageGenerationException('Invalid stub provided.');
        }
        try {
            $phar->setStub($stub);
        } catch (PharException $e) {
            throw new DPackageGenerationException('Invalid stub provided.');
        }
    }

    /**
     * Tests the provided package contents to ensure a package
     * can be generated from them.
     *
     * @param    ArrayIterator $contents      The package contents.
     * @param    DManifest     $manifest      The package manifest.
     * @param    int           $size          Pointer in which the total
     *                                        file size of the package
     *                                        contents will be returned.
     *
     * @return    DResult
     */
    protected function testContents(ArrayIterator $contents,
                                    DManifest $manifest, &$size)
    {
        return new DResult();
    }
}
