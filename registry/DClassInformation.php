<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\configuration\DApplicationMode;
use app\decibel\file\DFile;
use app\decibel\reflection\DReflectionClass;
use app\decibel\registry\DInvalidClassNameException;
use ReflectionException;

/**
 * Registers information about the classes available within a Decibel App.
 *
 * @author        Timothy de Paris
 */
class DClassInformation extends DRegistryHive
{
    /**
     * Filter bit for class information retrieval functions to only return
     * abstract classes.
     *
     * @var        int
     */
    const FILTER_ABSTRACT = 2;

    /**
     * Filter bit for class information retrieval functions to only return
     * concrete (non-abstract) classes.
     *
     * @var        int
     */
    const FILTER_CONCRETE = 1;

    /**
     * Filter bit for class information retrieval functions to only return
     * leaf classes (that is, they are not extended by any other class).
     *
     * @var        int
     */
    const FILTER_LEAF = 4;

    /**
     * Regular expression for extracting class
     * information from a filename.
     *
     * @var        string
     */
    const REGEX_CLASS = '/^(app(?:\/[a-zA-Z][a-zA-Z0-9]+)+)\/([A-Z][a-zA-Z0-9]+)\.php$/';

    /**
     * Prefix for information stored about a classes children.
     *
     * @var        string
     */
    const TYPE_CHILDREN = 'c';

    /**
     * Prefix for the filer bits for a class.
     *
     * @var        string
     */
    const TYPE_FILTER_BITS = 'b';

    /**
     * Index of classes within the scope of the registry.
     *
     * @var        array
     */
    protected $classes = array();

    /**
     * Provides debugging output for this object.
     *
     * @return    array
     */
    public function generateDebug()
    {
        $debug = parent::generateDebug();
        $debug['classes'] = $this->classes;

        return $debug;
    }

    /**
     * Prepares the object to be serialized.
     *
     * @return    array    List of properties to be serialized.
     */
    public function __sleep()
    {
        $sleep = parent::__sleep();
        $sleep[] = 'classes';

        return $sleep;
    }

    /**
     * Filters a list of class names.
     *
     * @param    array $classes Pointer to the class names to be filtered.
     * @param    int   $filter  The bitwise filter to apply.
     *
     * @return    void
     */
    protected function filterClassNames(array &$classes, $filter)
    {
        foreach ($classes as $key => $class) {
            if (!($this->classes[ $class ][ self::TYPE_FILTER_BITS ] & $filter)) {
                unset($classes[ $key ]);
            }
        }
    }

    /**
     * Generates a checksum for the registry hive contents.
     *
     * @return    string
     */
    protected function generateChecksum()
    {
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);

        return $fileInformation->getChecksum();
    }

    /**
     * Returns the qualified names of available classes.
     *
     * @note
     * By default, only concrete (non-abstract) classes will be returned.
     * This behaviour can be changed by using the <code>$filter</code>
     * parameter.
     *
     * @param    string $ancestor     If provided, only classes extending this
     *                                ancestor class will be returned.
     * @param    bool   $filter       Bitwise filter to apply to the returned
     *                                classes. Can include:
     *                                - {@link DClassInformation::FILTER_ABSTRACT}
     *                                - {@link DClassInformation::FILTER_CONCRETE}
     *                                - {@link DClassInformation::FILTER_LEAF}
     *
     * @return    array    List of qualified class names.
     * @throws    DInvalidClassNameException    If the specified ancestor class
     *                                        does not exist.
     */
    public function getClassNames($ancestor = null, $filter = self::FILTER_CONCRETE)
    {
        if ($ancestor === null) {
            $classes = array_keys($this->classes);
        } else {
            if (!isset($this->classes[ $ancestor ])) {
                // If this is the global registry, the requested ancestor must
                // exist - otherwise don't be so strict as this App could have
                // a dependency on another.
                if ($this->registry instanceof DGlobalRegistry) {
                    throw new DInvalidClassNameException($ancestor);
                } else {
                    $classes = array();
                }
            } else {
                $classes = $this->classes[ $ancestor ][ self::TYPE_CHILDREN ];
            }
        }
        if ($filter) {
            $this->filterClassNames($classes, $filter);
        }

        return $classes;
    }

    /**
     * Returns the qualified names of registry hives that this hive
     * is dependent on.
     *
     * @return    array    List of qualified names.
     */
    public function getDependencies()
    {
        return array(DFileInformation::class);
    }

    /**
     * Generates and throws an exception describing detected issues
     * while including a class.
     *
     * @param    string $namespace            Expected class namespace.
     * @param    string $className            Expected class name.
     * @param    string $filename             Name of the file in which
     *                                        the class is defined.
     * @param    array  $existingClasses      List of all classes that were
     *                                        available before this file was
     *                                        included.
     *
     * @return    void
     * @throws    DIncorrectClassNameException
     * @throws    DIncorrectNamespaceException
     * @throws    DEmptyClassFileException
     */
    protected function getClassDefinitionException($namespace, $className,
                                                   $filename, array &$existingClasses)
    {
        $newClasses = array_diff(
            get_declared_classes(),
            $existingClasses
        );
        if (count($newClasses) === 0) {
            throw new DEmptyClassFileException(
                DECIBEL_PATH . $filename
            );
        }
        $declaredClass = array_pop($newClasses);
        // What about multiple classes in a file?
        $declaredClassParts = explode('\\', $declaredClass);
        $declaredClassName = array_pop($declaredClassParts);
        $declaredNamespace = implode('\\', $declaredClassParts);
        if ($className !== $declaredClassName) {
            throw new DIncorrectClassNameException(
                $className,
                $declaredClassName,
                DECIBEL_PATH . $filename
            );
        } else {
            throw new DIncorrectNamespaceException(
                rtrim($namespace, '\\'),
                $declaredNamespace,
                DECIBEL_PATH . $filename
            );
        }
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
     * Determines if a class name is available within an installed App.
     *
     * @param    string $qualifiedName Qualified class name.
     *
     * @return    bool
     */
    public function isValidClass($qualifiedName)
    {
        return isset($this->classes[ $qualifiedName ]);
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
        if (!$hive instanceof DClassInformation) {
            return false;
        }
        $this->classes = array_merge_recursive(
            $this->classes,
            $hive->classes
        );
        // Make sure filter bits are merged correctly.
        foreach ($this->classes as &$class) {
            if (is_array($class[ self::TYPE_FILTER_BITS ])) {
                $class[ self::TYPE_FILTER_BITS ] = array_sum($class[ self::TYPE_FILTER_BITS ]);
            }
        }

        return true;
    }

    /**
     * Compiles data to be stored within the registry hive.
     *
     * @return    void
     * @throws    DIncorrectClassNameException
     * @throws    DIncorrectNamespaceException
     */
    protected function rebuild()
    {
        $this->classes = [];
        /* $this->loadFromClassmap() || */ $this->loadFromFileInformation();
    }

    /**
     *
     * @param    string $filename
     * @param    string $qualifiedName
     * @param    bool   $validateClassName
     *
     * @return DReflectionClass A reflection of the included class.
     * @throws DEmptyClassFileException
     * @throws DIncorrectClassNameException
     * @throws DIncorrectNamespaceException
     * @internal param string $namespace
     * @internal param string $className
     */
    protected function reflectClass($filename, $qualifiedName, $validateClassName = true)
    {
        $classInfo = new DClassInfo($qualifiedName);

        // Attempt to reflect the class. This may throw an exception
        // if the namespace of the class does not match the location
        // on the file system, or if the name of the class does not
        // match the file name.
        try {
            return new DReflectionClass("{$classInfo->namespace}\\{$classInfo->className}");
        } catch (ReflectionException $exception) {
            // If validating, track declared classes in case an incorrect
            // definition is found. This will allow us to provide a more
            // meaningful error message.
            if ($validateClassName) {
                $declaredClasses = get_declared_classes();
                $this->getClassDefinitionException(
                    $classInfo->namespace,
                    $classInfo->className,
                    $filename,
                    $declaredClasses
                );
            }
        }
        throw new DEmptyClassFileException($filename);
    }

    /**
     * Adds a class to the registry.
     *
     * @param    string $filename      Name of the file that defines the class.
     * @param    string $qualifiedName QUALIFIED_NAME | ::class
     *
     * @return bool <code>true</code> if the class was registered,
     *                    <code>false</code> if not.
     * @throws DEmptyClassFileException
     * @internal param string $namespace Namespace of the class.
     * @internal param string $classname Name of the class.
     *
     */
    protected function registerClass($filename, $qualifiedName)
    {
        // Reflect the class.
        $reflection = $this->reflectClass(
            $filename,
            $qualifiedName,
            DApplicationMode::isDebugMode()
        );
        // Ignore model definitions
        if ($reflection->isSubclassOf('app\\decibel\\model\\DBaseModel_Definition')) {
            return false;
        }
        // An entry will be made for all classes.
        $qualifiedName = $reflection->name;
        set_default($this->classes[ $qualifiedName ], array(
            self::TYPE_CHILDREN    => array(),
            // Initialise the filter bits, by default all classes will be considered
            // leaf classes until an extending class is registered.
            self::TYPE_FILTER_BITS => self::FILTER_LEAF,
        ));
        // Register information for abstract classes.
        if ($reflection->isAbstract()) {
            $this->classes[ $qualifiedName ][ self::TYPE_FILTER_BITS ]
                |= self::FILTER_ABSTRACT;
            // Register information for concrete classes.
        } else {
            $this->classes[ $qualifiedName ][ self::TYPE_FILTER_BITS ]
                |= self::FILTER_CONCRETE;
        }
        // Determine inheritance for the class.
        $classParents = array_merge(
            $reflection->getInterfaceNames(),
            $reflection->getParentNames(false),
            $reflection->getTraitNames()
        );
        foreach ($classParents as $classParent) {
            if (strpos($classParent, 'app\\') === 0) {
                set_default($this->classes[ $classParent ], array(
                    self::TYPE_CHILDREN    => array(),
                    // Initialise the filter bits, by default all classes will be considered
                    // leaf classes until an extending class is registered.
                    self::TYPE_FILTER_BITS => self::FILTER_LEAF,
                ));
                $this->classes[ $classParent ][ self::TYPE_CHILDREN ][]
                    = $qualifiedName;
                // Remove the leaf bit from the parent if it exists,
                // as the parent is obviously no longer a leaf class.
                $this->classes[ $classParent ][ self::TYPE_FILTER_BITS ]
                    &= ~self::FILTER_LEAF;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function loadFromClassmap()
    {
        if (file_exists(DECIBEL_PATH . 'vendor/composer/autoload_classmap.php')) {
            $classMap = include DECIBEL_PATH . 'vendor/composer/autoload_classmap.php';
            foreach ($classMap as $qualifiedName => $filename) {
                // register the new class
                $this->registerClass($filename, $qualifiedName);
            }
        }

        return !empty($this->classes);
    }

    /**
     * @return bool
     * @throws DInvalidDependencyException
     */
    private function loadFromFileInformation()
    {
        /* @var $fileInformation DFileInformation */
        $fileInformation = $this->getDependency(DFileInformation::class);
        $matches = null;
        foreach ($fileInformation->getFiles() as $filename) {
            $filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
            // PSR-0
            if (preg_match(self::REGEX_CLASS, $filename, $matches)) {
                $qualifiedName = str_replace('/',
                                             NAMESPACE_SEPARATOR,
                                             $matches[1])
                    . NAMESPACE_SEPARATOR . $matches[2];
                $this->registerClass($filename, $qualifiedName);
            }
            // workaround for PSR-4 support during testing
            else if (DApplicationMode::isTestMode() && DECIBEL_COMPOSER_ENABLED) {
                $map = include DECIBEL_PATH . 'vendor/composer/autoload_psr4.php';
                foreach ($map as $namespace => $dir) {
                    if (strpos($namespace, 'app\\') !== 0 || strpos($filename, 'vendor') !== false) {
                        continue;
                    }
                    $pathname     = DFile::correctSlashFor($dir[0]) . $filename;
                    // construct the PSR-0 compliant file in unix format
                    $filenamePsr0 = str_replace(NAMESPACE_SEPARATOR, '/', $namespace)
                                  . $filename;
                    if (preg_match(self::REGEX_CLASS, $filenamePsr0, $matches) &&
                        file_exists($pathname)
                    ) {
                        $qualifiedName = str_replace('/', NAMESPACE_SEPARATOR, $matches[1])
                                       . NAMESPACE_SEPARATOR . $matches[2];
                        $this->registerClass($pathname, $qualifiedName);
                        break;
                    }
                }
            }
        }
        return !empty($this->classes);
    }
}
