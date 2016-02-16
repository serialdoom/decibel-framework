<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

/**
 * Handles an exception occurring when working with a Decibel package.
 *
 * @author        Timothy de Paris
 */
class DInvalidPackageException extends DPackagingException
{
    /**
     * Creates a new {@link DInvalidPackageException}.
     *
     * @param    string $location Location from which the package was loaded.
     * @param    string $reason   Reason for the package not being valid.
     *
     * @return    static
     */
    public function __construct($location, $reason)
    {
        parent::__construct(array(
                                'location' => $location,
                                'reason'   => $reason,
                            ));
    }
}
