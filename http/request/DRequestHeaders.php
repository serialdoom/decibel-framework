<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

use app\decibel\http\DHeaderList;

/**
 * Represents a list of HTTP request headers.
 *
 * @author    Timothy de Paris
 */
class DRequestHeaders extends DHeaderList
{
    /**
     * Returns a list of languages that will be accepted by the client.
     *
     * @note
     * This method parses the content of the <code>Accept-Language</code> header.
     *
     * @code
     * use app\decibel\http\request\DRequest;
     *
     * $request = DRequest::load();
     * $requestHeaders = $request->getHeaders();
     *
     * // Iterate over accepted languages.
     * foreach ($requestHeaders->getAcceptedLanguages() as $languageCode) {
     *    // Do something.
     * }
     * @endcode
     *
     * @return    array    List of accepted languages, or an empty array if the
     *                    <code>Accept-Language</code> header was not present
     *                    in the request.
     */
    public function getAcceptedLanguages()
    {
        $acceptLanguageHeader = $this->getHeader('Accept-Language');
        if ($acceptLanguageHeader) {
            $acceptLanguageHeader = explode(';', $acceptLanguageHeader);
            $languages = explode(',', strtolower($acceptLanguageHeader[0]));
        } else {
            $languages = array();
        }

        return $languages;
    }

    /**
     * Returns the proxied IP address for the request, as indicated by the
     * <code>X-Forwarded-For</code> or <code>X-Real-Ip</code> request headers.
     *
     * @return    string    The proxied IP address, or <code>null</code> if no proxied
     *                    IP address was detected.
     */
    public function getProxiedIpAddress()
    {
        $ipAddress = $this->getHeader('X-Forwarded-For');
        if (!$ipAddress) {
            $ipAddress = $this->getHeader('X-Real-Ip');
        }

        return $ipAddress;
    }
}
