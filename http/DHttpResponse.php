<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\configuration\DApplicationMode;
use app\decibel\router\DOnHttpResponse;
use app\decibel\stream\DHttpStream;
use app\decibel\stream\DOutputStream;
use app\decibel\stream\DSeekableStream;
use Exception;
use JsonSerializable;

/**
 * The {@link DHttpResponse} class is a base class for all possible
 * HTTP responses that may be sent to the client following execution
 * of a request.
 *
 * This class extends the PHP Exception class, allowing the response
 * to be thrown from the point of generation, then either caught
 * at a higher level, or if not caught sooner, executed and returned
 * to the client by the {@link app::decibel::router::DRouter DRouter}.
 *
 * @author    Timothy de Paris
 */
abstract class DHttpResponse extends Exception implements JsonSerializable
{
    /**
     * The HTTP response body to be sent to the client.
     *
     * @var        DSeekableStream
     */
    protected $body;

    /**
     * Number of seconds until this response should expire
     * from the client cache.
     *
     * @var        int
     */
    protected $cacheExpiry;

    /**
     * Character set in which the response body is encoded, if applicable.
     *
     * @var        string
     */
    protected $charset;

    /**
     * Custom headers to send with the response.
     *
     * @var        array
     */
    protected $customHeaders = array();

    /**
     * MIME type of the response body, if applicable.
     *
     * @var        string
     */
    protected $mimeType;

    /**
     * Exposes internal configuration
     * @return array
     */
    public function generateDebug()
    {
        return [
            'headers' => $this->prepareHeaders(),
        ];
    }

    /**
     * @return array of headers
     */
    private function prepareHeaders()
    {
        // adds the X-Decibel-Source header
        // Nothing can be cached in debug mode.
        if (DApplicationMode::isDebugMode()) {
            $this->addHeader('X-Decibel-Source', "{$this->getFile()}:{$this->getLine()}");
        }
        // send response
        return array_merge(
            $this->customHeaders,
            $this->getMimeHeaders(),
            $this->getResponseHeaders(),
            $this->getCacheHeaders()
        );
    }

    /**
     * Adds a custom header to this response.
     *
     * @note
     * This will be overriden by any headers with the same name generated by:
     * - {@link DHttpResponse::getMimeHeaders()}
     * - {@link DHttpResponse::getResponseHeaders()}
     * - {@link DHttpResponse::getCacheHeaders()}
     *
     * @param    string $name  Name of the header.
     * @param    string $value Header value.
     *
     * @return    DHttpResponse    For chaining.
     */
    public function addHeader($name, $value)
    {
        $this->customHeaders[ $name ] = $value;
    }

    /**
     * Executes the response and sends to the client.
     *
     * @param    DHttpStream $stream  Stream to write the response to.
     *                                If not provided, the default HTTP response
     *                                stream will be used (that is, the response
     *                                will be sent to the client).
     *
     * @return    void
     */
    public function execute(DHttpStream $stream = null)
    {
        if ($stream === null) {
            $stream = new DOutputStream();
        }

        $stream->setHeader($this->getResponseType());
        $stream->setHeaders($this->prepareHeaders());

        // Send the body content, if any.
        $this->writeBody($stream);
        // Don't allow execution to continue.
        // Usually this should be the last thing to happen,
        // as it will be caught in DRouter and executed,
        // however it is possible for someone call this
        // method themselves.
        exit;
    }

    /**
     * Returns the content that will be returned as the response body.
     *
     * @return    string    The response body, or <code>null</code>
     *                    if no body should be returned.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns caching headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getCacheHeaders()
    {
        $headers = array();
        if ($this->cacheExpiry > 0) {
            $expiryTime = time() + $this->cacheExpiry;
            $cacheControl = 'public';
        } else {
            // Expire a day ago.
            $expiryTime = time() - 86400;
            $cacheControl = 'no-cache, must-revalidate, private';
        }
        $headers['Expires'] = gmdate('D, d M Y H:i:s', $expiryTime) . ' GMT';
        $headers['Cache-Control'] = $cacheControl;

        return $headers;
    }

    /**
     * Returns the number of bytes that will be returned in the response body.
     *
     * @return    int        The number of bytes in the response body.
     */
    protected function getContentLength()
    {
        if ($this->body !== null) {
            $length = $this->body->getLength();
        } else {
            $length = 0;
        }

        return $length;
    }

    /**
     * Returns any applicable MIME headers for the response.
     *
     * @return    string
     */
    protected function getMimeHeaders()
    {
        $headers = array();
        if ($this->mimeType !== null) {
            $headers['Content-Type'] = $this->mimeType;
            if ($this->charset) {
                $headers['Content-Type'] .= '; charset=' . $this->charset;
            }
        }
        $length = $this->getContentLength();
        if ($length) {
            $headers['Content-Length'] = $length;
        }

        return $headers;
    }

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return array List of header/value pairs.
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
    abstract public function getResponseType();

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    abstract public function getStatusCode();

    /**
     * Returns an array ready for encoding into json format.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            '_qualifiedName' => get_class(),
            'message'        => $this->message,
        ];
        // Determine which properties to show - only show the message if
        // we are not in debug mode.
        if (DApplicationMode::isDebugMode()) {
            $data['file'] = $this->file;
            $data['line'] = $this->line;
        }
        return $data;
    }

    /**
     * Sets content that will be used as the response body.
     *
     * @param    DSeekableStream $body        The response body.
     * @param    string          $mimeType    MIME type of the response body.
     * @param    string          $charset     Character set in which the response
     *                                        is encoded.
     *
     * @return    static    This object, for chaining.
     */
    public function setBody(DSeekableStream $body, $mimeType = null,
                            $charset = 'UTF-8')
    {
        $this->body = $body;
        $this->mimeType = $mimeType;
        $this->charset = $charset;

        return $this;
    }

    /**
     * Sets the cache expiry for this response.
     *
     * @param    int $expiresIn       The number of seconds until this response
     *                                should expire from the client cache.
     *                                If <code>null</code> is provided,
     *                                the request will never expire.
     *
     * @return    static
     */
    public function setCacheExpiry($expiresIn = null)
    {
        // Nothing can be cached in debug mode.
        if (DApplicationMode::isDebugMode()) {
            $expiresIn = null;
        }
        // Check that the expiry time doesn't exceed the maximum configured
        // cache lifetime.
        $maxLifetime = 86400;
        if ($expiresIn > $maxLifetime) {
            $this->cacheExpiry = $maxLifetime;
        } else {
            $this->cacheExpiry = $expiresIn;
        }

        return $this;
    }

    /**
     * Shows the debug console for redirectable responses,
     * when debug mode is enabled.
     *
     * @param    DOnHttpResponse $event
     *
     * @return    void
     */
    public static function showDebugConsole(DOnHttpResponse $event)
    {
        $response = $event->getResponse();
        if ($response instanceof DRedirectableResponse
            && $response->getRedirectReason()
            && DApplicationMode::isDebugMode()
        ) {
            include_once(DECIBEL_PATH . 'app/decibel/_view/debug/RedirectConsole.php');
            echo generateRedirectConsole($response);
            exit;
        }
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
            while ($content = $body->read(4096)) {
                $stream->write($content);
            }
        }
    }
}
