<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when a class inherits from a class
 * or interface that it cannot extend or implement.
 *
 * @author        Timothy de Paris
 */
class DInvalidClassInheritanceException extends DClassInformationException
{
    /**
     * Creates a new {@link DInvalidClassInheritanceException}.
     *
     * @param    string $className            The class name.
     * @param    string $parentClassName      The invalid parent class name.
     * @param    string $reason               Optional reason explaining why
     *                                        the inheritance is not possible.
     *
     * @return    static
     */
    public function __construct($className, $parentClassName,
                                $reason = null)
    {
        if (interface_exists($parentClassName)) {
            $inheritanceType = 'implement';
        } else {
            $inheritanceType = 'extend';
        }
        parent::__construct(array(
                                'className'       => $className,
                                'parentClassName' => $parentClassName,
                                'inheritanceType' => $inheritanceType,
                                'reason'          => (string)$reason,
                            ));
    }
}
