<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
// @requires	translation
namespace app\decibel\packaging;

/**
 * Handles an exception occurring when a package cannot be compressed.
 *
 * @author        Timothy de Paris
 */
class DPackageCompressionException extends DPackagingException
{
    /**
     * Creates a new {@link DPackageCompressionException}.
     *
     * @param    string $reason
     *
     * @return    static
     */
    public function __construct($reason = '')
    {
        parent::__construct(array(
                                'reason' => $reason,
                            ));
    }
}
