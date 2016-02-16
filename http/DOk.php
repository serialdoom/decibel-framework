<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\http\request\DRequest;
use app\decibel\stream\DHttpStream;

/**
 * Allows a successful (200) response to be returned to the browser.
 *
 * @author    Timothy de Paris
 */
class DOk extends DHttpResponse
{
    /**
     * 'OK' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 200;
    /**
     * 'OK' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 200 OK';

    /**
     * Executes the response and sends to the client.
     *
     * @param    DHttpStream $stream Stream to write the response to.
     *
     * @return    void
     */
    public function execute(DHttpStream $stream = null)
    {
        // Check to see if the client requested a content range.
        $range = DRequest::load()
                         ->getHeaders()
                         ->getHeader('Range');
        // If so, return a Partial Content (206) response.
        if ($range !== null) {
            $partialContent = new DPartialContent($this, $range);
            if ($partialContent->isPartial()) {
                $partialContent->execute($stream);
            } else {
                parent::execute($stream);
            }
            // Otherwise, continue with the 200 response.
        } else {
            parent::execute($stream);
        }
    }

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getResponseHeaders()
    {
        $headers = array();
        $headers['Accept-Ranges'] = 'bytes';

        return $headers;
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
