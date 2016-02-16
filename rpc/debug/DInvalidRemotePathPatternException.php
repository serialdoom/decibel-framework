<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc\debug;

/**
 * Handles an exception occurring when an invalid pattern is used
 * to create a {@link DRemotePath} object.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DInvalidRemotePathPatternException extends DRpcException
{
    /**
     * Creates a new {@link DInvalidRemotePathPatternException}.
     *
     * @param    string           $path   The requested path.
     * @param    DRemoteProcedure $state  State that rejected the path.
     * @param    string           $reason Explanation of why the path is invalid.
     *
     * @return    static
     */
    public function __construct($path, DRemoteProcedure $state, $reason)
    {
        parent::__construct(array(
                                'path'   => $path,
                                'state'  => get_class($state),
                                'reason' => $reason,
                            ));
    }
}
