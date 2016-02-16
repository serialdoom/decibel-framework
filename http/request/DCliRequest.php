<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

/**
 * Request wrapper for a PHP CLI request.
 *
 * @section   versioning Version Control
 *
 * @author    Timothy de Paris
 */
class DCliRequest extends DRequest
{
    /**
     * 'CLI' virtual method.
     *
     * @var        string
     */
    const METHOD = 'CLI';

    /**
     * Builds the URL for this request.
     *
     * @param    DRequestInformation $information
     *
     * @throws    DMalformedUrlException
     * @return    DUrl
     */
    protected function buildUrl(DRequestInformation $information)
    {
        // Building the URL for a CLI request
        // would cause a malformed URL exception.
        return null;
    }
}
