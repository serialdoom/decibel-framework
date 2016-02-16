<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\http\request\DRequestParameters;
use app\decibel\model\debug\DInvalidFieldValueException;
use app\decibel\model\field\DArrayField;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DIntegerField;
use app\decibel\model\field\DTextField;
use app\decibel\regional\DLabel;
use app\decibel\utility\DUtilityData;

/**
 * Represents a URL.
 *
 * @author        Timothy de Paris
 */
class DUrl extends DUtilityData
{
    /**
     * 'Fragment' field name.
     *
     * @var        string
     */
    const FIELD_FRAGMENT = 'fragment';
    /**
     * 'Hostname' field name.
     *
     * @var        string
     */
    const FIELD_HOSTNAME = 'hostname';
    /**
     * 'Protocol' field name.
     *
     * @var        string
     */
    const FIELD_PROTOCOL = 'protocol';
    /**
     * 'Port' field name.
     *
     * @var        string
     */
    const FIELD_PORT = 'port';
    /**
     * 'Query Parameters' field name.
     *
     * @var        string
     */
    const FIELD_QUERY_PARAMETERS = 'queryParameters';
    /**
     * 'URI' field name.
     *
     * @var        string
     */
    const FIELD_URI = 'uri';
    /**
     * Regular expression component to validate a protocol.
     *
     * @var        string
     */
    const REGEX_PROTOCOL = '[A-Za-z]';
    /**
     * Regular expression component to validate a host name.
     *
     * @var        string
     */
    const REGEX_HOSTNAME = '[[:alnum:]\-]+(\.[[:alnum:]\-]+)*';
    /**
     * Regular expression component to validate one or more folders.
     *
     * @note
     * Valid characters for folders within a URL taken from RFC1738, section 2.2
     * See http://www.ietf.org/rfc/rfc1738.txt for further information.
     *
     * @var        string
     */
    const REGEX_FOLDERS = '(\/[[:alnum:]\%\$\-\_\.\+\!\*\'\(\)\,]+)*';
    /**
     * Regular expression component to validate URL.
     *
     * @var        string
     */
    const REGEX_URI = '[#:\?]';
    /**
     * Regular expression component to validate Query String.
     *
     * @var        string
     */
    const REGEX_QUERYSTRING = '(\?.*)?';
    /**
     * Regular expression component to validate Bookmark.
     *
     * @var        string
     */
    const REGEX_FRAGMENT = '[^#]*';
    /**
     * Regular expression component to validate a file extension.
     *
     * @note
     * Valid characters for file names within a URL taken from RFC1738, section 2.2
     * See http://www.ietf.org/rfc/rfc1738.txt for further information.
     *
     * @var        string
     */
    const REGEX_FILE = '(\.[[:alnum:]\%\$\-\_\.\+\!\*\'\(\)\,]+)';
    /**
     * Regular expression component to validate an optional trailing slash.
     *
     * @var        string
     */
    const REGEX_SLASH_OPTIONAL = '\/?';
    /**
     * Regular expression component to validate a required trailing slash.
     *
     * @var        string
     */
    const REGEX_SLASH_REQUIRED = '\/';
    /**
     * Represents the HTTP protocol
     *
     * @var        string
     */
    const PROTOCOL_HTTP = 'http';
    /**
     * Represents the HTTPS protocol
     *
     * @var        string
     */
    const PROTOCOL_HTTPS = 'https';

    /**
     * Creates a new {@link DUrl} object.
     *
     * @param    string  $uri         The URI.
     * @param    boolean $normalise   If set to <code>true</code>, normalisation of the URL
     *                                will occure during parsing (for example, adding
     *                                a trailing slash).
     *
     * @return    static
     */
    public function __construct($uri, $normalise = true)
    {
        parent::__construct();
        $this->setUri($uri, $normalise);
    }

    /**
     * Creates a new {@link DUrl} object.
     *
     * @param    string $uri The URI.
     *
     * @return    static
     */
    public static function create($uri)
    {
        return new static($uri);
    }

    /**
     * Returns a string representation of this URL.
     *
     * @return    string
     */
    public function __toString()
    {
        $stringValue = $this->getBaseUrlString();
        if ($this->getFieldValue(self::FIELD_QUERY_PARAMETERS)) {
            $stringValue .= '?' . $this->getQueryString();
        }
        $fragment = $this->getFieldValue(self::FIELD_FRAGMENT);
        if ($fragment) {
            $stringValue .= "#{$fragment}";
        }

        return $stringValue;
    }

    /**
     * Returns the URL string, not including any query parameters or fragments.
     *
     * @return    string
     */
    public function getBaseUrlString()
    {
        return $this->getWebsiteRoot()
        . ltrim($this->getFieldValue(self::FIELD_URI), '/');
    }

    /**
     * Returns the root for the URL's domain, including protocol and port if applicable.
     *
     * @return    string
     */
    public function getWebsiteRoot()
    {
        $stringValue = '';
        if (!$this->isRelative()) {
            $stringValue .= "{$this->getFieldValue(self::FIELD_PROTOCOL)}://";
        }
        $stringValue .= $this->getFieldValue(self::FIELD_HOSTNAME);
        // Add port only if we have the protocol and hostname
        $port = $this->getFieldValue(self::FIELD_PORT);
        if ($stringValue && $port) {
            $stringValue .= ":{$port}";
        }

        return "{$stringValue}/";
    }

    /**
     * Defines fields available for this utility data object
     *
     * This function should call the {@link DUtilityData::addField()} function.
     *
     * @return    void
     */
    protected function define()
    {
        $labelNone = new DLabel('app\\decibel', 'none');
        $labelUnknown = new DLabel('app\\decibel', 'unknown');
        $protocol = new DEnumStringField(self::FIELD_PROTOCOL, 'Protocol');
        $protocol->setNullOption($labelUnknown);
        $protocol->setValues(DUrl::getAvailableProtocols());
        $protocol->setDefault(null);
        $this->addField($protocol);
        $port = new DIntegerField(self::FIELD_PORT, 'Port');
        $port->setNullOption($labelUnknown);
        $port->setUnsigned(true);
        $port->setSize(4);
        $port->setNullOption(null);
        $port->setDefault(null);
        $this->addField($port);
        $hostname = new DTextField(self::FIELD_HOSTNAME, 'Host Name');
        $hostname->setNullOption($labelUnknown);
        $hostname->setMaxLength(255);
        $hostname->setValidationRegex('/^' . DUrl::REGEX_HOSTNAME . '$/');
        $hostname->setDefault(null);
        $this->addField($hostname);
        $uri = new DTextField(self::FIELD_URI, 'URI');
        $uri->setMaxLength(2048);
        $this->addField($uri);
        $queryParameters = new DArrayField(self::FIELD_QUERY_PARAMETERS, 'Query Parameters');
        $queryParameters->setNullOption($labelNone);
        $this->addField($queryParameters);
        $fragment = new DTextField(self::FIELD_FRAGMENT, 'Fragment');
        $fragment->setNullOption($labelNone);
        $fragment->setMaxLength(255);
        $fragment->setValidationRegex('/^' . DUrl::REGEX_FRAGMENT . '$/');
        $fragment->setDefault(null);
        $this->addField($fragment);
    }

    /**
     * Returns a list of available protocols for URLs.
     *
     * @return    array
     */
    public static function getAvailableProtocols()
    {
        return array(
            DUrl::PROTOCOL_HTTP  => 'HTTP',
            DUrl::PROTOCOL_HTTPS => 'HTTPS',
        );
    }

    /**
     * Returns the fragment for this URL.
     *
     * @return    string
     */
    public function getFragment()
    {
        return $this->getFieldValue(self::FIELD_FRAGMENT);
    }

    /**
     * Returns the hostname for this URL.
     *
     * @return    string
     */
    public function getHostname()
    {
        return $this->getFieldValue(self::FIELD_HOSTNAME);
    }

    /**
     * Returns the port for this URL.
     *
     * @return    integer
     */
    public function getPort()
    {
        return $this->getFieldValue(self::FIELD_PORT);
    }

    /**
     * Returns the protocol for this URL.
     *
     * @return    string
     */
    public function getProtocol()
    {
        return $this->getFieldValue(self::FIELD_PROTOCOL);
    }

    /**
     * Returns the query parameters for this URL.
     *
     * @return    array
     */
    public function getQueryParameters()
    {
        return $this->getFieldValue(self::FIELD_QUERY_PARAMETERS);
    }

    /**
     * Returns the query parameters for this URL formatted as a string.
     *
     * @return    string
     */
    public function getQueryString()
    {
        return DRequestParameters::buildQueryString(
            $this->getFieldValue(self::FIELD_QUERY_PARAMETERS)
        );
    }

    /**
     * Returns the URI for this URL.
     *
     * @return    string
     */
    public function getURI()
    {
        return $this->getFieldValue(self::FIELD_URI);
    }

    /**
     * Determines if this is a relative URL.
     *
     * @return    bool
     */
    public function isRelative()
    {
        return !$this->getFieldValue(self::FIELD_PROTOCOL)
        || !$this->getFieldValue(self::FIELD_HOSTNAME);
    }

    /**
     * Determines if this URL utilises the secure HTTPS protocol.
     *
     * @return    bool
     */
    public function isSecure()
    {
        return ($this->getFieldValue(self::FIELD_PROTOCOL) === DUrl::PROTOCOL_HTTPS);
    }

    /**
     * Sets the fragment for this URL.
     *
     * @param    string $fragment The fragment.
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setFragment($fragment = null)
    {
        $this->setFieldValue(self::FIELD_FRAGMENT, $fragment);

        return $this;
    }

    /**
     * Sets the hostname for this URL.
     *
     * @param    string $hostname The hostname.
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setHostname($hostname = null)
    {
        $this->setFieldValue(self::FIELD_HOSTNAME, $hostname);

        return $this;
    }

    /**
     * Sets the port for this URL.
     *
     * @param    int $port The port, or null if there is no port.
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setPort($port = null)
    {
        $this->setFieldValue(self::FIELD_PORT, $port);

        return $this;
    }

    /**
     * Sets the protocol for this URL.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * // Convert the current URL to HTTPS.
     * $url = $request->getUrl();
     * $url->setProtocol('https');
     * debug((string) $url);
     * @endcode
     *
     * @param    string $protocol The protocol.
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setProtocol($protocol = null)
    {
        $this->setFieldValue(self::FIELD_PROTOCOL, $protocol);

        return $this;
    }

    /**
     * Sets the query parameters for this URL.
     *
     * @param    array $queryParameters The query parameters.
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setQueryParameters(array $queryParameters)
    {
        $this->setFieldValue(self::FIELD_QUERY_PARAMETERS, $queryParameters);

        return $this;
    }

    /**
     * Sets the URI for this URL.
     *
     * @param    string  $uri         The URI.
     * @param    boolean $normalise   If set to <code>true</code>, normalisation of the URL
     *                                will occure during parsing (for example, adding
     *                                a trailing slash).
     *
     * @return    static
     * @throws    DInvalidFieldValueException If an invalid value is provided.
     */
    public function setURI($uri = '/', $normalise = true)
    {
        // Ensure that URI is valid
        if (preg_match('/' . DURL::REGEX_URI . '/', $uri)) {
            throw new DInvalidFieldValueException(
                $this->getField(self::FIELD_URI),
                'A valid URI.'
            );
        }
        // Ensure a trailing forward slash is present if URI is not file name.
        if ($normalise
            && strpos($uri, '.') === false
            && strrpos($uri, '/') !== strlen($uri) - 1
        ) {
            $uri .= '/';
        }
        $this->setFieldValue(self::FIELD_URI, $uri);

        return $this;
    }
}
