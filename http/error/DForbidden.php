<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

use app\decibel\security\DForbiddenRequestLog;
use app\decibel\stream\DHttpStream;

/**
 * Allows a forbidden (HTTP 403) response to be sent to the client.
 *
 * @note
 * A record of the forbidden request will be logged in the
 * {@link app::decibel::security::DForbiddenRequestLog DForbiddenRequestLog}.
 *
 * @author    Timothy de Paris
 */
class DForbidden extends DHttpError
{
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
        return 'HTTP/1.1 403 Forbidden';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 403;
    }
}
