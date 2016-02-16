<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\http\error\DRequestedRangeNotSatisfiable;
use app\decibel\stream\DHttpStream;

/**
 * Allows a partial content (206) response to be returned to the browser.
 *
 * @author    Timothy de Paris
 */
class DPartialContent extends DHttpResponse
{
    /**
     * 'OK' HTTP status code.
     *
     * @var        int
     */
    const STATUS_CODE = 206;

    /**
     * 'OK' HTTP status line.
     *
     * @var        string
     */
    const STATUS_LINE = 'HTTP/1.1 206 Partial Content';

    /**
     * Regular expression to match HTTP Range request.
     *
     * @var        string
     */
    const REGEX_RANGE = '/bytes=(?|([0-9]+)\-([0-9]+)|([0-9]+)\-()|()\-([0-9]+))/u';

    /**
     * Start byte of the range to return.
     *
     * @var        int
     */
    protected $rangeStart;

    /**
     * Length of content to return.
     *
     * @var        int
     */
    protected $rangeLength;

    /**
     * Creates a new {@link DPartialContent}.
     *
     * @param    DOk        The full response.
     * @param    string     "Range" header sent by the client.
     *                      Supported range headers are:
     *                      - "bytes=x-"
     *                      - "bytes=x-y"
     *                      - "bytes=-y"
     *
     * @return    static
     * @throws    DRequestedRangeNotSatisfiable
     */
    public function __construct(DOk $response, $range)
    {
        parent::__construct();
        $this->setCacheExpiry($response->cacheExpiry);
        $this->setBody(
            $response->body,
            $response->mimeType,
            $response->charset
        );
        $rangeMatches = null;
        $contentLength = parent::getContentLength();
        if (preg_match(self::REGEX_RANGE, $range, $rangeMatches)) {
            if ($rangeMatches[1] === '') {
                $this->rangeStart = $contentLength - (int)$rangeMatches[2];
                $this->rangeLength = (int)$rangeMatches[2];
            } else {
                if ($rangeMatches[2] === '') {
                    $this->rangeStart = (int)$rangeMatches[1];
                    $this->rangeLength = $contentLength - (int)$rangeMatches[1];
                } else {
                    $this->rangeStart = (int)$rangeMatches[1];
                    $this->rangeLength = (int)$rangeMatches[2] - (int)$rangeMatches[1] + 1;
                }
            }
        }
        // Test that the range is valid for the response body.
        if ($this->rangeStart === null
            || $this->rangeStart < 0
            || $this->rangeStart >= $contentLength
            || $this->rangeStart + $this->rangeLength > $contentLength
        ) {
            // Create and execute the error, this will end the script.
            $error = new DRequestedRangeNotSatisfiable($contentLength);
            $error->execute();
        }
    }

    /**
     * Returns the number of bytes that will be returned in the response body.
     *
     * @return    int
     */
    protected function getContentLength()
    {
        return $this->rangeLength;
    }

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getResponseHeaders()
    {
        $contentLength = parent::getContentLength();
        $rangeEnd = $this->rangeStart + $this->rangeLength - 1;
        $headers = array();
        $headers['Accept-Ranges'] = 'bytes';
        $headers['Content-Range'] = "bytes {$this->rangeStart}-{$rangeEnd}/{$contentLength}";

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

    /**
     * Determines if the requested range covers all content.
     *
     * @return    bool
     */
    public function isPartial()
    {
        return ($this->rangeLength !== parent::getContentLength());
    }

    /**
     * Writes the response body to the output stream.
     *
     * @param    DHttpStream $stream The output stream to write to.
     *
     * @return    void
     */
    protected function writeBody(DHttpStream $stream)
    {
        $body = $this->getBody();
        if ($body !== null) {
            $this->body->seek($this->rangeStart);
            $stream->write(
                $this->body->read($this->rangeLength)
            );
        }
    }
}
