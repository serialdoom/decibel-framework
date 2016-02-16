<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

use app\decibel\configuration\DApplicationMode;
use stdClass;

/**
 * Allows a redirect (HTTP 302) response to be sent to the client.
 *
 * See the @ref routing_redirects Developer Guide for further information about
 * issuing redirects in %Decibel.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DRedirect extends DHttpResponse
    implements DRedirectableResponse
{
    /**
     * The reason for the redirect.
     *
     * @var        string
     */
    protected $reason;

    /**
     * The URL to redirect to.
     *
     * @var        string
     */
    protected $url;

    /**
     * Creates a new {@link DRedirect}.
     *
     * @param    string $url      The URL to redirect to.
     * @param    string $reason   Reason for the redirect to be issued.
     *                            In {@link app::decibel::configuration::DApplicationMode::MODE_DEBUG Debug Mode}
     *                            the reason will be displayed before the redirect
     *                            is issued. If <code>null</code> is provided,
     *                            the redirect will always be issued immediately.
     *
     */
    public function __construct($url, $reason = 'No reason given.')
    {
        $this->url = $url;
        $this->reason = $reason;
    }

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getResponseHeaders()
    {
        $headers = array();
        $headers['Location'] = $this->url;

        return $headers;
    }

    /**
     * Returns the reason for the redirect being issued.
     *
     * @return    string
     */
    public function getRedirectReason()
    {
        return $this->reason;
    }

    /**
     * Returns the URL to which the client should be redirected.
     *
     * @return    string
     */
    public function getRedirectUrl()
    {
        return $this->url;
    }

    /**
     * Returns the HTTP response header to be sent to the client when
     * this response is executed.
     *
     * @return    string
     */
    public function getResponseType()
    {
        return 'HTTP/1.1 302 Found';
    }

    /**
     * Returns the HTTP status code for this type of response.
     *
     * @return    int
     */
    public function getStatusCode()
    {
        return 302;
    }

    /**
     * Returns an array ready for encoding into json format.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['url'] = $this->url;
        // only show the reason in debug mode
        if (DApplicationMode::isDebugMode()) {
            $data['reason'] = $this->reason;
        }
        return $data;
    }
}
