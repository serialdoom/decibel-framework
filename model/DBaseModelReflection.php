<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\health\DHealthCheckResult;
use app\decibel\model\search\DBaseModelSearch;
use app\decibel\reflection\DReflectionClass;
use app\decibel\registry\DClassQuery;

/**
 * Reflects a {@link DBaseModel} object.
 *
 * @author    Timothy de Paris
 */
class DBaseModelReflection extends DReflectionClass
{
    /**
     * Creates a reflection of the provided class
     *
     * @param    string $qualifiedName Qualified name of the class to reflect.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct($qualifiedName);
        $isAbstract = DClassQuery::load()
                                 ->setAncestor('app\\decibel\\utility\\DDefinable')
                                 ->isAbstract($qualifiedName);
        if (!$isAbstract) {
            $definition = DDefinition::load($qualifiedName);
            $this->fields = $definition->getFields();
        }
    }

    /**
     * Tests the implementation of this class.
     *
     * @note
     * This method can be overriden to implement custom tests for special
     * reflection classes.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult} objects.
     */
    protected function performImplementationTest()
    {
        $results = array();
        $qualifiedName = $this->getQualifiedName();
        if (!$qualifiedName::link() instanceof DBaseModelSearch) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_ERROR,
                "<code>{$qualifiedName}::link()</code> must return an instance of <code>app\\decibel\\model\\search\\DBaseModelSearch</code>."
            );
        }

        return $results;
    }
}
