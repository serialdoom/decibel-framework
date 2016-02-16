<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

use app\decibel\packaging\DPackagingException;

/**
 * Handles an exception occurring when loading a Decibel update repository.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DInvalidRepositoryException extends DPackagingException
{
    /**
     * Creates a new {@link DInvalidRepositoryException}.
     *
     * @param    string $location Location from which the repository was loaded.
     * @param    string $reason   Reason for the repository not being valid.
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
