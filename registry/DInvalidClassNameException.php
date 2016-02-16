<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

/**
 * Handles an exception occurring when information about an unknown ancestor
 * class is requested.
 *
 * @author        Timothy de Paris
 */
class DInvalidClassNameException extends DRegistryException
{
    /**
     * Creates a new {@link DInvalidClassNameException}.
     *
     * @param    string $qualifiedName Qualfied name of the class.
     *
     * @return    static
     */
    public function __construct($qualifiedName)
    {
        parent::__construct(array(
                                'qualifiedName' => $qualifiedName,
                            ));
    }
}
