<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

/**
 * Provides default HTTP request information to the {@link DRequest} object.
 *
 * @author    Timothy de Paris
 */
class DDefaultRequestInformation implements DRequestInformation
{
    /**
     * List of headers provided by the request, stored here after first
     * loaded by the {@link DDefaultRequestInformation::getHeaders()} method.
     *
     * @var        DRequestHeaders
     */
    protected $headers;

    /**
     * The host for this request, loaded from the PHP $_SERVER array.
     *
     * @var        string
     */
    protected $host;

    /**
     * Creates a new {@link DDefaultRequestInformation} object.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->host = filter_input(INPUT_SERVER, 'HTTP_HOST');
    }

    /**
     * Returns arguments provided to the script if executed via CLI.
     *
     * @return    array
     */
    public function getArguments()
    {
        return $GLOBALS['argv'];
    }

    /**
     * Returns the request body.
     *
     * @return    string
     */
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    /**
     * Determines URL parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getUrlParameters()
    {
        return new DRequestParameters(
            (array)filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        );
    }

    /**
     * Returns a list of request headers.
     *
     * @return    DRequestHeaders
     */
    public function getHeaders()
    {
        if ($this->headers === null) {
            // The function doesn't exist in CLI.
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
            } else {
                $headers = array();
            }
            $this->headers = new DRequestHeaders($headers, true);
        }

        return $this->headers;
    }

    /**
     * Determines the host for this request.
     *
     * @return    string
     */
    public function getHost()
    {
        if ($this->host) {
            $host = preg_replace('/:.*/', '', $this->host);
        } else {
            $host = DCliRequest::METHOD;
        }

        return $host;
    }

    /**
     * Determines the request method.
     *
     * @return    string
     */
    public function getMethod()
    {
        $method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        if (!$method) {
            $method = DCliRequest::METHOD;
        }

        return $method;
    }

    /**
     * Returns the port through which this request was served.
     *
     * @return    int
     */
    public function getPort()
    {
        if ($this->host
            && strpos($this->host, ':') !== false
        ) {
            $port = (int)preg_replace('/.*:/', '', $this->host);
        } else {
            $port = null;
        }

        return $port;
    }

    /**
     * Determines POST parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getPostParameters()
    {
        return new DRequestParameters(
            (array)filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        );
    }

    /**
     * Determines the protocol for this request.
     *
     * @return    string
     */
    public function getProtocol()
    {
        if ($this->host === null) {
            $protocol = DRequest::PROTOCOL_FILE;
        } else {
            if (isset($_SERVER['HTTPS'])) {
                $protocol = DRequest::PROTOCOL_HTTPS;
            } else {
                $protocol = DRequest::PROTOCOL_HTTP;
            }
        }

        return $protocol;
    }

    /**
     * Determines the referer of the request.
     *
     * @return    string
     */
    public function getReferer()
    {
        $referer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        if ($referer) {
            $referer = urldecode($referer);
        }

        return $referer;
    }

    /**
     * Returns a list of uploaded files.
     *
     * @return    DFileUploads    List of {@link DFileUpload} objects.
     */
    public function getUploadedFiles()
    {
        return new DFileUploads($_FILES);
    }

    /**
     * Determines the request URI.
     *
     * @note
     * This must exclude any preceding forward slash and query parameters.
     *
     * @return    string
     */
    public function getUri()
    {
        $requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        if ($requestUri) {
            $uri = urldecode(
                preg_replace('/(^\/|\?.*$)/', '', $requestUri)
            );
        } else {
            $uri = null;
        }

        return $uri;
    }
}
