<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

/**
 * Allows an Internal Server Error (HTTP 500) response to be sent to the client.
 *
 * @author    Timothy de Paris
 */
class DInternalServerError extends DHttpError
{
    /**
     * 'Bad Request' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 500;

    /**
     * 'Bad Request' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 500 Internal Server Error';

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
