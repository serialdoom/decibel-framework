<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\error;

use app\decibel\http\DHttpResponse;
use app\decibel\http\DRedirectableResponse;
use app\decibel\stream\DTextStream;
use app\decibel\utility\DString;

/**
 * Represents an HTTP error status code that a redirect can be issued
 * alongside of to improve user experience.
 *
 * @author    Timothy de Paris
 */
abstract class DHttpError extends DHttpResponse
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
    protected $redirect;

    /**
     * Creates a new {@link DHttpError}.
     *
     * @param    string $redirect     The URL to redirect to, if any.
     * @param    string $reason       Reason for the error.
     *                                In {@link app::decibel::configuration::DApplicationMode::MODE_DEBUG Debug Mode}
     *                                the reason will be displayed before any redirect
     *                                is issued. If <code>null</code> is provided,
     *                                any redirect will always be issued immediately.
     *
     * @return    static
     */
    public function __construct($redirect = null, $reason = null)
    {
        $this->redirect = $redirect;
        $this->reason = $reason;
        // Generate the default response body.
        // This can be overriden later using the setResponseBody method.
        if ($redirect) {
            $this->body = self::generateMetaRedirect($redirect);
        }
    }

    /**
     * Generates a meta redirect response body for the provided url.
     *
     * @param    string $url The URL to redirect to.
     *
     * @return    DTextStream
     */
    protected static function generateMetaRedirect($url)
    {
        $body = "<meta http-equiv=\"refresh\" content=\"0; url={$url}\" />";
        // IE requires the size of the gzipped response to be greater
        // than 512 bytes, otherwise it may override the error page
        // and not execute the redirect. So just send some random content.
        $body .= '<div style="display: none;">';
        $body .= DString::getRandomString(2000);
        $body .= '</div>';

        return new DTextStream($body);
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
        return $this->redirect;
    }

    /**
     * Returns a list of headers to be sent to the client.
     *
     * @return    array    List of header/value pairs.
     */
    protected function getResponseHeaders()
    {
        return array();
    }
}
