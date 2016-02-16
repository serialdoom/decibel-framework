<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\ssl;

use app\decibel\debug\DDebuggable;

/**
 * Base class for OpenSSL resources including keys and certificates.
 *
 * @author    Timothy de Paris
 */
abstract class DSslResource implements DDebuggable
{
    /**
     * The SSL resource.
     *
     * @var        resource
     */
    protected $resource;

    /**
     * Returns the private key resource.
     *
     * @return    resource
     */
    protected function getResource()
    {
        return $this->resource;
    }
}
