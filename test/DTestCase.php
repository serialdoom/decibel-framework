<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

use app\decibel\regional\DLabel;
use app\decibel\registry\DClassInfo;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Base class for all Decibel test cases.
 *
 * @author    David Stevens
 */
abstract class DTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @param DLabel $label
     * @param string $name
     * @param string $namespace
     */
    public function assertLabel($label, $name = '', $namespace = '')
    {
        $this->assertInstanceOf(DLabel::class, $label);
        if ($namespace) {
            $this->assertSame($namespace, $label->getNamespace());
        }
        if ($name) {
            $this->assertSame($name, $label->getName());
        }
    }

    /**
     * Workaround to be able to test the way Decibel works with
     * ::class in testcases.
     *
     * @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/134
     *
     * @param string $originalQualifiedName
     * @param array  $arguments
     *
     * @return mixed
     */
    public function getMockForAbstractClass($originalQualifiedName, array $arguments = [],
                                            $mockClassName = '', $callOriginalConstructor = true,
                                            $callOriginalClone = true, $callAutoload = true,
                                            $mockedMethods = [], $cloneArguments = false)
    {
        $classInfo = new DClassInfo($originalQualifiedName);
        $mock = parent::getMockForAbstractClass($originalQualifiedName, [],
                                                $mockClassName, $callOriginalConstructor,
                                                $callOriginalClone, $callAutoload,
                                                $mockedMethods, $cloneArguments);
        $mockClassName = get_class($mock);
        $mockQualifiedName = $classInfo->namespace
                           . NAMESPACE_SEPARATOR . $mockClassName;
        if (!class_exists($mockQualifiedName)) {
            $namespace = $classInfo->getNamespace(false);
            // http://stackoverflow.com/questions/9229605/in-php-how-do-you-get-the-called-aliased-class-when-using-class-alias
            $exec = "namespace $namespace { class $mockClassName extends \\$mockClassName {} }";
            eval($exec);
        }

        $reflectionClass = new ReflectionClass($mockQualifiedName);
        return $reflectionClass->newInstanceArgs($arguments);
    }
}
