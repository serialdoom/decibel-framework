<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

/**
 * Allows a permanent redirect (HTTP 301) response to be sent to the client.
 *
 * See the @ref routing_redirects Developer Guide for further information about
 * issuing redirects in %Decibel.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DPermanentRedirect extends DRedirect
{
    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return 'HTTP/1.1 301 Moved Permanently';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 301;
    }
}
