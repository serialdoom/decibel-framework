<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

/**
 * Allows a not found (HTTP 404) response to be sent to the client.
 *
 * @author        Timothy de Paris
 */
class DNotFound extends DHttpError
{
    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return 'HTTP/1.1 404 Not Found';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 404;
    }
}
