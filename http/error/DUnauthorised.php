<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

use app\decibel\security\DForbiddenRequestLog;
use app\decibel\stream\DHttpStream;

/**
 * Allows a unauthorised (HTTP 401) response to be sent to the client.
 *
 * @note
 * A record of the forbidden request will be logged
 * in the {@link DForbiddenRequestLog}.
 *
 * @author    Timothy de Paris
 */
class DUnauthorised extends DHttpError
{
    /**
     * 'Bad Request' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 401;

    /**
     * 'Bad Request' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 401 Unauthorized';

    /**
     * Executes the response and sends to the client.
     *
     * @param    DHttpStream $stream Stream to write the response to.
     *
     * @return    void
     */
    public function execute(DHttpStream $stream = null)
    {
        // Log a record in the WAF log.
        DForbiddenRequestLog::log(array(
                                      'reason' => $this->reason,
                                  ));
        parent::execute($stream);
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
