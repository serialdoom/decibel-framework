<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\test;

use app\decibel\http\request\DFileUploads;
use app\decibel\http\request\DRequestHeaders;
use app\decibel\http\request\DRequestInformation;
use app\decibel\http\request\DRequestParameters;

/**
 * Allows specific request information to be overriden for testing purposes.
 *
 * @author    Timothy de Paris
 */
class DTestRequestInformation implements DRequestInformation
{
    /**
     * Overriden arguments.
     *
     * @var        array
     */
    protected $arguments;
    /**
     * Uploaded files.
     *
     * @var        DFileUploads
     */
    protected $files;
    /**
     * Overriden headers.
     *
     * @var        DRequestHeaders
     */
    protected $headers;
    /**
     * Overriden host.
     *
     * @var        string
     */
    protected $host;
    /**
     * Overriden method.
     *
     * @var        string
     */
    protected $method;
    /**
     * Overriden port.
     *
     * @var        int
     */
    protected $port;
    /**
     * Overriden POST parameters.
     *
     * @var        DRequestParameters
     */
    protected $postParameters;
    /**
     * Overriden protocol.
     *
     * @var        string
     */
    protected $protocol;
    /**
     * Overriden referer.
     *
     * @var        string
     */
    protected $referer;
    /**
     * Overriden URI.
     *
     * @var        string
     */
    protected $uri;
    /**
     * Overriden URL parameters.
     *
     * @var        DRequestParameters
     */
    protected $urlParameters;

    /**
     * Creates a new {@link DTestRequestInformation} object.
     *
     * @return    static
     */
    public function __construct()
    {
        $this->files = new DFileUploads();
        $this->urlParameters = new DRequestParameters();
        $this->postParameters = new DRequestParameters();
    }

    /**
     * Creates a new {@link DDefaultRequestInformation}.
     *
     * @return    static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Returns arguments provided to the script if executed via CLI.
     *
     * @return    array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Returns the request body.
     *
     * @return    string
     */
    public function getBody()
    {
        return '';
    }

    /**
     * Returns URL parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getUrlParameters()
    {
        return $this->urlParameters;
    }

    /**
     * Returns a list of request headers.
     *
     * @return    DRequestHeaders
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Determines the host for this request.
     *
     * @return    string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Determines the request method.
     *
     * @return    string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the port through which this request was served.
     *
     * @return    int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Determines POST parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getPostParameters()
    {
        return $this->postParameters;
    }

    /**
     * Determines the protocol for this request.
     *
     * @return    string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Determines the referer of the request.
     *
     * @return    string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * Returns a list of uploaded files.
     *
     * @return    DFileUploads    List of {@link app::decibel::http::DFileUpload DFileUpload} objects.
     */
    public function getUploadedFiles()
    {
        return $this->files;
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
        return $this->uri;
    }

    /**
     * Overrides default arguments.
     *
     * @param    array $arguments
     *
     * @return    static
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Overrides default URL parameters.
     *
     * @param    DRequestParameters $parameters
     *
     * @return    static
     */
    public function setUrlParameters(DRequestParameters $parameters)
    {
        $this->urlParmeters = $parameters;

        return $this;
    }

    /**
     * Overrides default headers.
     *
     * @param    DRequestHeaders $headers
     *
     * @return    static
     */
    public function setHeaders(DRequestHeaders $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Overrides default host.
     *
     * @param    string $host
     *
     * @return    static
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Overrides default method.
     *
     * @param    string $method
     *
     * @return    static
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Overrides default port.
     *
     * @param    int $port
     *
     * @return    static
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Overrides default POST parameters.
     *
     * @param    DRequestParameters $parameters
     *
     * @return    static
     */
    public function setPostParameters(DRequestParameters $parameters)
    {
        $this->postParameters = $parameters;

        return $this;
    }

    /**
     * Overrides default protocol.
     *
     * @param    string $protocol
     *
     * @return    static
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Overrides default referer.
     *
     * @param    string $referer
     *
     * @return    static
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;

        return $this;
    }

    /**
     * Overrides default uploaded files.
     *
     * @param    DFileUploads $uploadedFiles
     *
     * @return    static
     */
    public function setUploadedFiles(DFileUploads $uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    /**
     * Overrides default URI.
     *
     * @param    string $uri
     *
     * @return    static
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }
}
