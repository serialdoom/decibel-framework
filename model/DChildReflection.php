<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\model;

use app\decibel\application\DClassManager;
use app\decibel\health\DHealthCheckResult;
use app\decibel\model\DBaseModelReflection;
use app\decibel\model\DChild;
use app\decibel\registry\DClassQuery;

/**
 * Reflects a {@link DChild} object.
 *
 * @author    Timothy de Paris
 */
class DChildReflection extends DBaseModelReflection
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
            $instance = $qualifiedName::create();
            $this->fields = $instance->getFields();
        }
    }

    /**
     * Tests the implementation of this class against best practice
     * for %Decibel Apps.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult} objects.
     */
    public function performImplementationTest()
    {
        $results = parent::performImplementationTest();
        $qualifiedName = $this->getQualifiedName();
        $definition = $qualifiedName::getDefinition();
        // Test the DChild::OPTION_PARENT_OBJECT option is set.
        $parentQualifiedName = $definition->getOption(DChild::OPTION_PARENT_OBJECT);
        if (!$parentQualifiedName
            || !DClassManager::isValidClassName($parentQualifiedName)
        ) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_ERROR,
                "The <code>app\decibel\model\DChild::OPTION_PARENT_OBJECT</code> option must be set to the qualified name of a valid model within the definition of <code>{$qualifiedName}</code>."
            );
        }

        return $results;
    }
}
