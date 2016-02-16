<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

/**
 * Allows a Requested Range Not Satisfiable (HTTP 416) response
 * to be sent to the client.
 *
 * @author    Timothy de Paris
 */
class DRequestedRangeNotSatisfiable extends DHttpError
{
    /**
     * 'Bad Request' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 416;

    /**
     * 'Bad Request' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 416 Requested Range Not Satisfiable';

    /**
     * Creates a new {@link DRequestedRangeNotSatisfiable}.
     *
     * @param    int $contentLength Size of the requested content.
     *
     * @return    static
     */
    public function __construct($contentLength)
    {
        parent::__construct();
        $lastByte = $contentLength - 1;
        $this->addHeader('Content-Range', "bytes 0-{$lastByte}/{$contentLength}");
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
