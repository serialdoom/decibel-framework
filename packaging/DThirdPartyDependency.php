<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

use app\decibel\regional\DLabel;

/**
 * Describes a dependency of a %Decibel package on a third-party
 * script or resource.
 *
 * @section        versioning Version Control
 *
 * @author         Timothy de Paris
 * @ingroup        packaging
 */
class DThirdPartyDependency extends DDependency
{
    /**
     * Name of the third-party component.
     *
     * @var        string
     */
    protected $name;
    /**
     * Version of the third-party component.
     *
     * @var        string
     */
    protected $version;
    /**
     * Location (URL) of the third-party component.
     *
     * @var        string
     */
    protected $location;
    /**
     * Licence of the third-party component.
     *
     * @var        string
     */
    protected $licence;

    /**
     * Returns a string describing this DDependency.
     *
     * @return    string
     */
    public function __toString()
    {
        $label = new DLabel(
            'app\\decibel\\packaging\\DThirdPartyDependency',
            'displayName'
        );

        return (string)$label;
    }

    /**
     * Determines if the current state of the pre-requisite meets the provided
     * criteria.
     *
     * @param    string $value The value to compare.
     *
     * @return    bool
     */
    protected function compareTo($value)
    {
        return true;
    }

    /**
     * Returns the current state of this pre-requisite.
     *
     * @return    mixed
     */
    public function getCurrentState()
    {
        return null;
    }

    /**
     * Returns a message describing a pre-requisite failure.
     *
     * @return    string
     */
    protected function getMessage()
    {
        return null;
    }
}
