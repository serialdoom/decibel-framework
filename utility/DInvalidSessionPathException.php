<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * Handles an exception occurring when an invalid session path is used.
 *
 * @author        Timothy de Paris
 */
class DInvalidSessionPathException extends DSessionException
{
    /**
     * Creates a new {@link DInvalidSessionPathException}.
     *
     * @param    string $path The invalid session path.
     *
     * @return    static
     */
    public function __construct($path)
    {
        $server = DServer::load();
        parent::__construct(array(
                                'path'     => $path,
                                'username' => $server->getProcessUsername(),
                            ));
    }
}
