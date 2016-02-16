<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\debug\DDebuggable;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DPharHive;

/**
 * A registry hive contains a specific set of information,
 * stored within a {@link DRegistry}.
 *
 * @author        Timothy de Paris
 */
abstract class DRegistryHive implements DPharHive, DDispatchable, DDebuggable
{
    use DBaseClass;
    use DEventDispatcher;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::registry::DOnHiveUpdate DOnHiveUpdate}
     * event.
     *
     * @var        string
     */
    const ON_UPDATE = DOnHiveUpdate::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::registry::DOnGlobalHiveUpdate DOnGlobalHiveUpdate}
     * event.
     *
     * @var        string
     */
    const ON_GLOBAL_UPDATE = DOnGlobalHiveUpdate::class;

    /**
     * Checksum for this registry.
     *
     * @var        string
     */
    protected $checksum;

    /**
     * Hives on which this registry is dependent.
     *
     * @var        array    List of {@link DRegistryHive} objects.
     */
    protected $dependencies;

    /**
     * The format version of the registry hive, when serialized to disk.
     *
     * @var        int
     */
    protected $formatVersion;

    /**
     * The registry to which this hive belongs.
     *
     * @var        DRegistry
     */
    protected $registry;

    /**
     * Whether the registry hive has been updated.
     *
     * @var        bool
     */
    protected $updated = false;

    /**
     * Creates a new {@link DRegistryHive}
     *
     * @param    DRegistry $registry The registry to which this hive belongs.
     *
     * @return    static
     */
    public function __construct(DRegistry $registry)
    {
        $this->initialise($registry);
    }

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        return array(
            'registry'     => (string)$this->registry,
            'dependencies' => $this->getDependencies(),
            'checksum'     => $this->checksum,
            'updated'      => $this->updated,
        );
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        return array(
            'checksum',
            'formatVersion',
        );
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return self::ON_UPDATE;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_UPDATE,
            self::ON_GLOBAL_UPDATE,
        );
    }

    /**
     * Initialises the registry hive.
     *
     * @note
     * This method is automatically called by the constructor, however
     * is public to allow re-initilisation of the registry when unserialized
     * from disk.
     *
     * @param    DRegistry $registry The registry to which this hive belongs.
     *
     * @return mixed
     */
    public function initialise(DRegistry $registry)
    {
        $this->registry = $registry;
        $this->dependencies = array();
        foreach ($this->getDependencies() as $dependency) {
            $this->dependencies[ $dependency ] = $registry->getHive($dependency);
        }
        // Don't allow rebuild of a Global registry hive.
        if ($registry instanceof DGlobalRegistry) {
            return null;
        }
        $checksum = null;
        if ($this->requiresRebuild($checksum)) {
            $this->checksum = $checksum;
            $this->updated = true;
            $this->rebuild();
            // Set the current format version following rebuild.
            $this->formatVersion = $this->getFormatVersion();
        }
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    abstract protected function generateChecksum();

    /**
     * Returns the checksum for this registry hive.
     *
     * @return    string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array();
    }

    /**
     * Returns a registry hive on which this hive is dependent.
     *
     * @param    string $qualifiedName Qualified name of the dependency to return.
     *
     * @return    DRegistryHive
     * @throws    DInvalidDependencyException    If the provided qualified name is not
     *                                        that of a valid dependency for this
     *                                        registry hive.
     */
    public function getDependency($qualifiedName)
    {
        if (!isset($this->dependencies[ $qualifiedName ])) {
            throw new DInvalidDependencyException($this, $qualifiedName);
        }

        return $this->dependencies[ $qualifiedName ];
    }

    /**
     * Returns a version number indicating the format of the registry.
     *
     * This number is used to ensure that the registry hive written to disk
     * is of the same format as the current registry hive class.
     *
     * If the format of the serialized registry hive is changed, the version
     * number returned by this function must also be incremented to ensure
     * consistency.
     *
     * @return    int
     */
    abstract public function getFormatVersion();

    /**
     * Returns the registry to which this hive belongs.
     *
     * @return    DRegistry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Determines whether any of the depedencies of this registry have been
     * updated since this registry was built.
     *
     * @return    bool
     */
    public function hasUpdatedDependency()
    {
        $updated = false;
        foreach ($this->dependencies as $dependency) {
            /* @var $dependency DRegistryHive */
            if ($dependency->isUpdated()) {
                $updated = true;
                break;
            }
        }

        return $updated;
    }

    /**
     * Determines if the hive has updated it's content since being loaded.
     *
     * @return    bool    <code>true</code> if the hive has been updated,
     *                    <code>false</code> if not.
     */
    public function isUpdated()
    {
        return $this->updated;
    }

    /**
     * Merges the provided registry hive into this registry hive.
     *
     * @param    DRegistryHive $hive The hive to merge into this hive.
     *
     * @return    bool
     */
    abstract public function merge(DRegistryHive $hive);

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     */
    abstract protected function rebuild();

    /**
     * Determines if this registry hives needs to be rebuilt.
     *
     * Hives generally need to be rebuilt only if the data they represent
     * has been modified.
     *
     * @note
     * This method will always returns <code>false</code> when Decibel
     * is running in production mode or test mode.
     *
     * @param    string $checksum     Pointer in which the generated checksum
     *                                will be returned, if this registry needs
     *                                to be rebuilt.
     *
     * @return    bool    <code>true</code> if this hive needs to be rebuilt,
     *                    <code>false</code> if not.
     */
    public function requiresRebuild(&$checksum = null)
    {
        // If the format version is out of sync, we need to updated
        // regardless of the current application mode. This could occur
        // when the new registry enabled core is installed into an instance
        // containing Apps with no registry - a rebuild of these App registries
        // will initially be required.
        $formatVersion = $this->getFormatVersion();
        if ($this->formatVersion !== $formatVersion) {
            $requiresRebuild = true;
            // Check if the registry can be re-built.
        } else {
            if (!$this->registry->canRebuild()) {
                $requiresRebuild = false;
            } else {
                // The checksum is the main condition for rebuild.
                $checksum = $this->generateChecksum($checksum);
                if ($this->checksum !== $checksum) {
                    $requiresRebuild = true;
                    // If any of the dependencies have been updated, this hive
                    // will probably need to be rebuilt also.
                } else {
                    $requiresRebuild = $this->hasUpdatedDependency();
                }
            }
        }

        return $requiresRebuild;
    }

    /**
     * Triggers the update events for this hive.
     *
     * @param    DRegistry $registry The registry the hive was set against.
     *
     * @return    void
     */
    public function triggerUpdate(DRegistry $registry)
    {
        if ($registry instanceof DGlobalRegistry) {
            $event = new DOnGlobalHiveUpdate();
        } else {
            $event = new DOnHiveUpdate();
        }
        $event->setHive($this);
        $this->notifyObservers($event);
    }
}
