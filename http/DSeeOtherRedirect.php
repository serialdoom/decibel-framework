<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

/**
 * Allows a temporary redirect (HTTP 303) response to be sent to the client.
 *
 * @note
 * This type of redirect is always issued as a GET request.
 *
 * See the @ref routing_redirects Developer Guide for further information about
 * issuing redirects in %Decibel.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DSeeOtherRedirect extends DRedirect
{
    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return 'HTTP/1.1 303 See Other';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 303;
    }
}
