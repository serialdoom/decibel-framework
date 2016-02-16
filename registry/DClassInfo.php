<?php
namespace app\decibel\registry;

/**
 * Class DClassInfo
 *
 * @author Alex van Andel <avanandel@decibeltechnology.com>
 * @package app\decibel\registry
 */
class DClassInfo
{
    /** @var string */
    public $namespace;

    /** @var string */
    public $className;

    /** @var bool */
    public $preserveGlobalNsScope = true;

    /**
     * DClassInfo constructor.
     *
     * @param string $qualifiedName
     */
    public function __construct($qualifiedName)
    {
        // extract the namespace and classname out of the qualified name
        $pos = strrpos($qualifiedName, NAMESPACE_SEPARATOR);
        // use the pos of the last \\ to determine the classname and namespace
        $this->namespace = substr($qualifiedName, 0, $pos);
        $this->className = substr($qualifiedName, $pos + 1);
    }

    /**
     * Fetches the NS of the supplied class
     *
     * @param bool $preserveGlobalNsScope default=true
     *                                    when set to false, will remove the global
     *                                    NS identifier like '\app\..' to 'app\'
     *
     * @return string
     */
    public function getNamespace($preserveGlobalNsScope = true)
    {
        $namespace = $this->namespace;
        if (!$preserveGlobalNsScope &&
            strpos($namespace, NAMESPACE_SEPARATOR) === 0
        ) {
            $namespace = substr($namespace, 1);
        }
        return $namespace;
    }

    /**
     * @return string full qualified name to this class
     */
    public function getQualifiedName()
    {
        return $this->namespace . NAMESPACE_SEPARATOR . $this->className;
    }
}
