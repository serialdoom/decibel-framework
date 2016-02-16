<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

/**
 * Allows a No Content (204) response to be returned to the browser.
 *
 * @author    Timothy de Paris
 */
class DNoContent extends DHttpResponse
{
    /**
     * 'No Content' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 204;

    /**
     * 'No Content' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 204 No Content';

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
