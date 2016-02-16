<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DDebuggable;
use app\decibel\debug\DErrorHandler;
use PharData;
use PharException;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

/**
 * A write-lockable Phar-based repository.
 *
 * @author        Timothy de Paris
 */
abstract class DPharRepository implements DDebuggable
{
    /**
     * The loaded repository archive.
     *
     * @var        PharData
     */
    protected $archive;
    /**
     * Location at which available registry hives are cached.
     *
     * @var        array
     */
    protected $availableHives;
    /**
     * Name of the file in which this repository is persisted to the filesystem.
     *
     * @var        string
     */
    protected $filename;
    /**
     * Location at which loaded registry hives are cached.
     *
     * @var        array
     */
    protected $loadedHives = array();
    /**
     * File handle for any lock held on this repository.
     *
     * @var        resource
     */
    protected $lock;
    /**
     * Name of the lock file for this repository.
     *
     * @var        string
     */
    protected $lockFile;
    /**
     * Relative path at which this repository is persisted to the filesystem.
     *
     * This path is taken from <code>DECIBEL_PATH</code>.
     *
     * @var        string
     */
    protected $relativePath;

    /**
     * Creates a {@link DPharRepository}
     *
     * @param    string $relativePath     Path to the repository, taken from
     *                                    <code>DECIBEL_PATH</code>
     * @param    string $filename         Repository filename.
     *
     * @return    static
     * @throws    UnexpectedValueException    If the Phar file could not be created or loaded.
     */
    protected function __construct($relativePath, $filename)
    {
        $this->relativePath = $relativePath;
        $this->filename = $filename;

        $absoluteFilename = $this->getFilename();
        $this->lockFile   = $this->getFilename() . '.lock';
        if (!file_exists($this->lockFile)) {
            file_put_contents($this->lockFile, '');
        }
        // Load the repository file.
        $this->archive = new PharData($absoluteFilename);
    }

    /**
     * Ensures any lock held by this registry is released on destruction.
     *
     * @return    void
     */
    public function __destruct()
    {
        $this->releaseLock();
    }

    /**
     * Returns a string representation of the {@link DRegistry} class.
     *
     * @return    string
     */
    public function __toString()
    {
        return get_class($this) . " ({$this->getRelativePath()})";
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'filename'       => $this->getFilename(),
            'availableHives' => $this->getAvailableHives(),
        );
    }

    /**
     * Returns the absolute path to this registry.
     *
     * @return    string
     */
    public function getAbsolutePath()
    {
        return (DECIBEL_PATH . $this->relativePath) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns a list of hives currently stored in this registry.
     *
     * @return    array    List of qualified names of stored
     *                    {@link DRegistryHive} objects.
     */
    public function getAvailableHives()
    {
        // Load from disk if this is the first request.
        if ($this->availableHives === null) {
            // Previously used $this->archive->getPathname(), however
            // sometimes this returned null.
            $root = 'phar://' . DECIBEL_PATH . $this->relativePath . $this->filename;
            $iterator = new RecursiveIteratorIterator($this->archive);
            $this->availableHives = array();
            foreach ($iterator as $hive) {
                /* @var $hive SplFileInfo */
                $filename = str_replace($root, '', $hive->getPathname());
                $this->availableHives[] = str_replace('/', '\\', trim($filename, '/'));
            }
        }

        return $this->availableHives;
    }

    /**
     * Returns the name of the registry file on the filesystem.
     *
     * @return    string
     */
    public function getFilename()
    {
        return DECIBEL_PATH . $this->filename;
    }

    /**
     * Retrieves a configuration from the repository.
     *
     * @param    string $qualifiedName Qualified name of the configuration to retrieve.
     *
     * @return    DConfiguration    The configuration, or <code>null</code> if the configuration
     *                            was not stored in the repository.
     */
    public function getHive($qualifiedName)
    {
        // If this is the first time the hive has been
        // requested, load it from disk.
        if (!isset($this->loadedHives[ $qualifiedName ])) {
            $hive = $this->loadHive($qualifiedName);
            $this->loadedHives[ $qualifiedName ] = $hive;
        }

        return $this->loadedHives[ $qualifiedName ];
    }

    /**
     * Acquires a lock on the registry.
     *
     * @param    bool $exclusive      Whether an exclusive lock is required.
     *                                This should be set to <code>true</code>
     *                                when writing to the registry.
     *
     * @return    void
     */
    protected function getLock($exclusive = false)
    {
        if ($exclusive) {
            $mode = 'w+';
            $operation = LOCK_EX;
            $debug = 'exclusive';
        } else {
            $mode = 'r+';
            $operation = LOCK_SH;
            $debug = 'shared';
        }
        $this->lock = fopen($this->lockFile, $mode);
        flock($this->lock, $operation);
        $this->lockStart = microtime(true);
    }

    /**
     * Returns the absolute path to this registry.
     *
     * @return    string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Determines if this registry stores a hive of the specified type.
     *
     * @param    string $qualifiedName Qualified name of the hive to check for.
     *
     * @return    bool
     */
    public function hasHive($qualifiedName)
    {
        return in_array(
            $qualifiedName,
            $this->getAvailableHives()
        );
    }

    /**
     * Determines if the specified hive has been loaded from disk
     * by the registry and is now stored in process memory.
     *
     * @param    string $qualifiedName Qualified name of the hive to check for.
     *
     * @return    bool
     */
    protected function isHiveLoaded($qualifiedName)
    {
        return array_key_exists(
            $qualifiedName,
            $this->loadedHives
        );
    }

    /**
     * Attempts to load a hive from the Phar archive.
     *
     * @param    string $qualifiedName Qualified name of the configuration to retrieve.
     *
     * @return    void
     */
    abstract protected function loadHive($qualifiedName);

    /**
     * Releases any lock held on the registry.
     *
     * @return    void
     */
    protected function releaseLock()
    {
        if (is_resource($this->lock)) {
            flock($this->lock, LOCK_UN);
            fclose($this->lock);
        }
    }

    /**
     * Stores the provided hive within the repository.
     *
     * @param    DPharHive $hive The hive to store.
     *
     * @return    bool
     */
    public function setHive(DPharHive $hive)
    {
        $qualifiedName = get_class($hive);
        $this->loadedHives[ $qualifiedName ] = $hive;
        $this->availableHives[] = $qualifiedName;
        // Obtain an exclusive lock on the registry
        // before starting to write any content.
        $this->getLock(true);
        $path = str_replace('\\', '/', $qualifiedName);
        try {
            $this->archive[ $path ] = serialize($hive);
            // If something goes wrong, log the error and continue.
        } catch (PharException $exception) {
            DErrorHandler::logException($exception);
        }
        // Release the exclusive lock so that other
        // processes can read content from the registry.
        $this->releaseLock();

        return true;
    }
}
