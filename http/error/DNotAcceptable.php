<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

/**
 * Allows a not acceptable (HTTP 406) response to be sent to the client.
 *
 * @author        Timothy de Paris
 */
class DNotAcceptable extends DHttpError
{
    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return 'HTTP/1.1 406 Not Acceptable';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 406;
    }
}
