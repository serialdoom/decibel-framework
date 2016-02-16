<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\packaging;

/**
 * Handles an exception occurring when a package cannot be generated.
 *
 * @author    Timothy de Paris
 */
class DPackageGenerationException extends DPackagingException
{
    /**
     * Creates a new {@link DPackageGenerationException}.
     *
     * @param    string $reason Reason for generation failing.
     *
     * @return    static
     */
    public function __construct($reason)
    {
        parent::__construct(array(
                                'reason' => $reason,
                            ));
    }
}
