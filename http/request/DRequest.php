<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\http\debug\DMalformedUrlException;
use app\decibel\http\DUrl;
use app\decibel\http\DUrlParser;
use app\decibel\http\error\DForbidden;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\security\DIpAddress;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;

/**
 * Wrapper class for access to information about the request,
 * including file uploads.
 *
 * @section       why Why Would I Use It?
 *
 * This class normalises access to information about provided to Decibel
 * by the client, providing an extra level of security and error handling.
 *
 * @section       how How Do I Use It?
 *
 * This singleton class can be loaded as follows:
 *
 * @code
 * use app\decibel\http\request\DRequest;
 *
 * $request = DRequest::load();
 * @endcode
 *
 * Once loaded, methods can be called as documented below.
 *
 * See the @ref utility_request Developer Guide for further information.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DRequest implements DSingleton
{
    use DBaseClass;
    use DSingletonClass;

    /**
     * 'file://' protocol.
     *
     * @var        string
     */
    const PROTOCOL_FILE = 'file';

    /**
     * 'http://' protocol.
     *
     * @var        string
     */
    const PROTOCOL_HTTP = 'http';

    /**
     * 'https://' protocol.
     *
     * @var        string
     */
    const PROTOCOL_HTTPS = 'https';

    /**
     * Mapping of HTTP methods to {@link DRequest} class types.
     *
     * @var        array
     */
    protected static $requestTypes = array(
        DGetRequest::METHOD    => DGetRequest::class,
        DPostRequest::METHOD   => DPostRequest::class,
        DPutRequest::METHOD    => DPutRequest::class,
        DDeleteRequest::METHOD => DDeleteRequest::class,
        DCliRequest::METHOD    => DCliRequest::class,
    );

    /**
     * The protocol this request was made to.
     *
     * This should be one of {@link DRequest::PROTOCOL_HTTP} or {@link DRequest::PROTOCOL_HTTPS}.
     *
     * @var        string
     */
    protected $protocol;

    /**
     * Injected request information object.
     *
     * @var        DRequestInformation
     */
    protected $information;

    /**
     * The request URI.
     *
     * @var        string
     */
    protected $uri;

    /**
     * Validated URL parameters from the request.
     *
     * @var        DRequestParameters
     */
    protected $urlParameters;

    /**
     * The method used by this request.
     *
     * @var        string
     */
    protected $method;

    /**
     * The requested URL.
     *
     * @var        DUrl
     */
    protected $url;

    /**
     * Returns an instance of the class constructed with the given parameters.
     *
     * @param    DRequestInformation    Information about the request. If not provided,
     *                                  a {@link DDefaultRequestInformation} object
     *                                  will be created to determine request information.
     *
     * @return    static
     * @throws    DMalformedUrlException    If the request URL is malformed.
     */
    public static function create(DRequestInformation $information = null)
    {
        if ($information === null) {
            $information = new DDefaultRequestInformation();
        }
        // Create the correct request based on the HTTP method used.
        $method = $information->getMethod();
        if (isset(static::$requestTypes[ $method ])) {
            $requestType = static::$requestTypes[ $method ];
        } else {
            $requestType = __CLASS__;
        }

        return new $requestType($information);
    }

    /**
     * Returns a singleton instance of the extending class.
     *
     * @return    static
     */
    public static function load()
    {
        $qualifiedName = __CLASS__;
        // Load if this is the first instantiation.
        if (!isset(static::$instances[ $qualifiedName ])) {
            static::$instances[ $qualifiedName ] = $qualifiedName::create();
            static::$instances[ $qualifiedName ]->__wakeup();
        }

        return static::$instances[ $qualifiedName ];
    }

    /**
     * Validates request variables ready for access by the application.
     *
     * @param    DRequestInformation    Information about the request. If not provided,
     *                                  a {@link DDefaultRequestInformation} object
     *                                  will be created to determine request information.
     *
     * @return    static
     * @throws    DMalformedUrlException    If the request URL is malformed.
     */
    protected function __construct(DRequestInformation $information)
    {
        $this->information = $information;
        $this->protocol = $information->getProtocol();
        $this->uri = $information->getUri();
        $this->urlParameters = $information->getUrlParameters();
        $this->url = $this->buildUrl($information);
    }

    /**
     * Called once the singleton object is loaded for the first time.
     *
     * @return    void
     * @throws    DForbidden    If an invalid character is detected in the URI
     *                        or a cross-site scripting attack is detected.
     */
    public function __wakeup()
    {
        // Detect cross site scripting attacks.
        $this->checkXSite($this->uri);
        foreach ($this->urlParameters as $key => $value) {
            $this->checkXSite($key);
        }
        // Detect invalid requests.
        $this->checkInvalidRequest($this->uri);
    }

    /**
     * Builds the URL for this request.
     *
     * @param    DRequestInformation $information
     *
     * @throws    DMalformedUrlException    If the request URL is malformed.
     * @return    DUrl
     */
    protected function buildUrl(DRequestInformation $information)
    {
        // Parse the request URL information and throw an exception
        // if any of this information is malformed.
        $host = $information->getHost();
        try {
            return DUrl::create($this->uri)
                       ->setProtocol($this->protocol)
                       ->setHostname($host)
                       ->setPort($information->getPort())
                       ->setQueryParameters($this->urlParameters->toArray());
        } catch (DInvalidFieldValueException $exception) {
            throw new DMalformedUrlException("{$this->protocol}://{$host}/{$this->uri}");
        }
    }

    /**
     * Tests a URI for possible cross-site scripting attacks.
     *
     * @param    string $requestUri The URI to test.
     *
     * @return    bool
     * @throws    DForbidden    If a cross-site scripting attack is detected.
     */
    public function checkXSite($requestUri)
    {
        $xsite = strpos($requestUri, '%3E') !== false
            || strpos($requestUri, '>') !== false;
        // Return 403 response if attack detected.
        if ($xsite) {
            throw new DForbidden($this->getUrl()->getWebsiteRoot(), 'Cross-site scripting attack detected.');
        }

        return $xsite;
    }

    /**
     * Tests a URI for invalid characters.
     *
     * These are ASCII chracters below Space (32), as well as Del (127)
     * See http://en.wikipedia.org/wiki/ASCII#ASCII_control_code_chart
     * for further information about ASCII character codes.
     *
     * @param    string $requestUri The URI to test.
     *
     * @return    bool
     * @throws    DForbidden    If an invalid character is detected.
     */
    public function checkInvalidRequest($requestUri)
    {
        // Retrieve a string containing unacceptable characters.
        $invalidChars = self::getInvalidUriChars();
        // Check if the request contains any invalid characters.
        $invalid = (strpbrk($requestUri, $invalidChars) !== false);
        // Return 403 response if attack detected.
        if ($invalid) {
            throw new DForbidden($this->getUrl()->getWebsiteRoot(), 'Invalid characters detected in URL.');
        }

        return $invalid;
    }

    /**
     * Returns a string containing ASCII characters that are not valid
     * for a request URI.
     *
     * These are ASCII chracters below Space (32), as well as Del (127)
     * See http://en.wikipedia.org/wiki/ASCII#ASCII_control_code_chart
     * for further information about ASCII character codes.
     *
     * @return    string
     */
    public static function getInvalidUriChars()
    {
        // Build a string containing unacceptable characters.
        // This includes all chracters below Space (32), as well as Del (127)
        // See http://en.wikipedia.org/wiki/ASCII#ASCII_control_code_chart
        // Looks stupid, but is pretty fast!
        $chrs = range(0, 31);
        $chrs[] = 127;

        return vsprintf(
            str_repeat('%c', 33),
            $chrs
        );
    }

    /**
     * Returns the client IP address from which this request initiated.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     *
     * debug($request->getIpAddress());
     * @endcode
     *
     * @return    string    The client IP address, or <code>null</code>
     *                    if the IP address is not known.
     */
    public function getIpAddress()
    {
        $ipAddress = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        if ($ipAddress) {
            // If IP is trusted, check if "X-Real-IP" header is present.
            if (DIpAddress::checkIpAddress($ipAddress, DIpAddress::FLAG_TRUSTED)) {
                $proxiedIpAddress = $this->getHeaders()->getProxiedIpAddress();
                if ($proxiedIpAddress) {
                    $ipAddress = $proxiedIpAddress;
                }
            }
        } else {
            $ipAddress = null;
        }

        return $ipAddress;
    }

    /**
     * Returns the HTTP method used to request the URL.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     *
     * debug($request->getMethod());
     * @endcode
     *
     * @return    string    The HTTP method used to request the URL,
     *                    for example 'GET' or 'POST'
     */
    public function getMethod()
    {
        return static::METHOD;
    }

    /**
     * Returns the page referer.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     *
     * debug($request->getRefererUrl());
     * @endcode
     *
     * @return    DUrl    The referer, or <code>null</code> if no referer
     *                    was provided by the client.
     */
    public function getRefererUrl()
    {
        try {
            $referer = DUrlParser::parse($this->information->getReferer());
        } catch (DMalformedUrlException $exception) {
            $referer = null;
        }

        return $referer;
    }

    /**
     * Returns a {@link app::decibel::site::DUrl DUrl} object representing
     * the requested URL.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     *
     * debug($request->getUrl());
     * @endcode
     *
     * @return    DUrl
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * This function returns HTTP request headers passed by the client.
     *
     * @return    DRequestHeaders
     */
    public function getHeaders()
    {
        return $this->information->getHeaders();
    }

    /**
     * Returns the request parameters for this request.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     *
     * // Calculate the total number of request parameters provided.
     * debug(count($request->getParameters()));
     * @endcode
     *
     * @return    DRequestParameters
     */
    public function getParameters()
    {
        return $this->urlParameters;
    }

    /**
     * Determines if the current request was loaded through the HTTPS protocol.
     *
     * @return    bool
     */
    public function isHttps()
    {
        return ($this->protocol === self::PROTOCOL_HTTPS);
    }

    /**
     * Setter for adding the uri to the DRequest object
     *
     * @param    string $uri Updated URI.
     *
     * @throws    DInvalidFieldValueException    If the value of a provided
     *                                        parameter is invalid.
     * @return    void
     */
    public function setUri($uri)
    {
        if ($this->url) {
            $this->url->setURI($uri);
        }
        $this->uri = $uri;
    }
}
