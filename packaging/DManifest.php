<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

use app\decibel\application\DConfigurationManager;
use app\decibel\utility\DResult;
use ArrayIterator;
use Phar;

/**
 * Describes a Decibel package.
 *
 * The package can be an installer, distribution or backup.
 *
 * @author    Timothy de Paris
 */
class DManifest
{
    /**
     * The package date.
     *
     * @var        int
     */
    protected $date;
    /**
     * A description of the package.
     *
     * @var        string
     */
    protected $description;
    /**
     * Qualified names of Apps contained in this package.
     *
     * @var        array
     */
    protected $plugins = array();
    /**
     * Name of the person or entity that created the package.
     *
     * @var        string
     */
    protected $creatorName;
    /**
     * Email address of the person or entity that created the package.
     *
     * @var        string
     */
    protected $creatorEmail;
    /**
     * The package signature, if this is signed.
     *
     * @var        string
     */
    protected $signature;
    /**
     * CRC32 checksum for each file in the package.
     *
     * @var        array
     */
    protected $checksums = array();

    /**
     * Creates a new manifest.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->date = time();
    }

    /**
     * Adds CRC32 checksums for each file in the package to the manifest.
     *
     * @param    ArrayIterator $contents Contents of the package.
     * @param    Phar          $phar     The phar containing the files.
     *
     * @return    DResult
     */
    public function addChecksums(ArrayIterator $contents, Phar $phar)
    {
        $result = new DResult('Checksums', 'calculated');
        // Determine the memory limit for the script.
        $configurationManager = DConfigurationManager::load();
        $internalMemoryLimit = $configurationManager->getInternalMemoryLimit();
        foreach ($contents as $basename => $pathname) {
            // Check that we have enough memory to do this!
            // @todo find a way to get the checksum of big files
            // when there isn't enough memory left.
            if (filesize($pathname) > ($internalMemoryLimit - memory_get_usage())) {
                $result->addMessage("Insufficient memory available to generate checksum for file <code>{$basename}</code>");
                continue;
            }
            $this->checksums[ $basename ] = crc32(file_get_contents($pathname));
            if ($this->checksums[ $basename ] !== crc32(file_get_contents($phar[ $basename ]))) {
                $result->setSuccess(false, "Invalid checksum for file <code>{$basename}</code>");
            }
            // Convert to an unsigned decimal number.
            $this->checksums[ $basename ] = sprintf('%u', $this->checksums[ $basename ]);
        }

        return $result;
    }

    /**
     * Adds a App to this manifest.
     *
     * @param    string $qualifiedName Qualified name of the App.
     *
     * @return    void
     */
    public function addApp($qualifiedName)
    {
        if (!in_array($qualifiedName, $this->plugins)) {
            $this->plugins[] = $qualifiedName;
        }
    }

    /**
     * Returns a list of qualified names of Apps included in this package.
     *
     * @return    array
     */
    public function getApps()
    {
        return $this->plugins;
    }

    /**
     * Returns the e-mail address of the package creator.
     *
     * @return    string
     */
    public function getCreatorEmail()
    {
        return $this->creatorEmail;
    }

    /**
     * Returns the name of the package creator.
     *
     * @return    string
     */
    public function getCreatorName()
    {
        return $this->creatorName;
    }

    /**
     * Returns the date and time at which the package was generated.
     *
     * @return    int        UNIX timestamp.
     */
    public function getCreatedTime()
    {
        return $this->date;
    }

    /**
     * Returns the description of this package.
     *
     * @return    string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Validates the checksum for a specified file.
     *
     * @param    string $basename Base name of the file within the package.
     * @param    string $filename Location of the extract file to be validated.
     *
     * @return    bool    <code>true</code> if the checksum is valid,
     *                    <code>false</code> if not.
     *                    If the provided basename does not have a checksum,
     *                    <code>null</code> will be returned.
     */
    public function validateChecksum($basename, $filename)
    {
        if (!isset($this->checksums[ $basename ])) {
            $valid = null;
        } else {
            if (!file_exists($filename)) {
                $valid = false;
            } else {
                $checksum = sprintf('%u', crc32(file_get_contents($filename)));
                $valid = ($this->checksums[ $basename ] === $checksum);
            }
        }

        return $valid;
    }
}
