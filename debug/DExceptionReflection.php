<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\debug;

use app\decibel\health\DHealthCheckResult;
use app\decibel\reflection\DReflectionClass;
use app\decibel\regional\DLabel;
use app\decibel\regional\DUnknownLabelException;

/**
 * Reflects a {@link DException} object.
 *
 * @author    Timothy de Paris
 */
class DExceptionReflection extends DReflectionClass
{
    /**
     * Tests the implementation of this class against best practice
     * for %Decibel Apps.
     *
     * @return    array    List of {@link app::decibel::health::DHealthCheckResult DHealthCheckResult} objects.
     */
    protected function performImplementationTest()
    {
        $results = parent::performImplementationTest();
        $qualifiedName = $this->getQualifiedName();
        // Check if this exception has a label defined.
        try {
            new DLabel($qualifiedName, 'message');
        } catch (DUnknownLabelException $exception) {
            $results[] = new DHealthCheckResult(
                DHealthCheckResult::HEALTH_CHECK_WARNING,
                "Label <code>{$qualifiedName}-message</code> must be defined to contain the message for this exception."
            );
        }

        return $results;
    }
}
