<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

/**
 * Allows a Not Modified (304) response to be returned to the client.
 *
 * @author    Timothy de Paris
 */
class DNotModified extends DHttpResponse
{
    /**
     * 'Not Modified' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 304;

    /**
     * 'Not Modified' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 304 Not Modified';

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getResponseHeaders()
    {
        return array();
    }

    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return self::STATUS_LINE;
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return self::STATUS_CODE;
    }
}
