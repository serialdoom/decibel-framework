<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\http\request\DRequest;

/**
 * Provides functionality for managing the session cookie.
 *
 * @author        Timothy de Paris
 */
class DSessionCookie
{
    /**
     * The domain on which session cookies will be set.
     *
     * Cached here after first access by the {@link DSessionCookie::getCookieDomain()}
     * method.
     *
     * @var        string
     */
    protected $cookieDomain;

    /**
     * Returns the domain on which the session cookie will be set
     * for this Decibel installation.
     *
     * @note
     * The returned domain will always be prefixed with a dot separator.
     *
     * @code
     * use app\decibel\utility\DSessionCookie;
     *
     * $sessionCookie = new DSessionCookie();
     * debug($sessionCookie->getCookieDomain());
     * @endcode
     *
     * The session cookie domain is determined as:
     * - The value of the <code>DECIBEL_CORE_SESSIONDOMAIN</code>
     *        configuration option.
     * - The calculated root domain (for example, if Decibel is running
     *        on the domain <code>www.mydomain.com</code>, the session cookie
     *        will be set on <code>.mydomain.com</code>
     *
     * @note
     * If the <code>DECIBEL_CORE_SESSIONDOMAIN</code> configuration option
     * contains an invalid domain (that is, the domain is not a root
     * of the current domain as returned by {@link DRequest::getHost()}),
     * the calculated root domain will be used.
     *
     * @return    string    The cookie domain, or <code>null</code> if no cookie
     *                    can be loaded (for example, this is a CLI request).
     */
    public function getCookieDomain()
    {
        if ($this->cookieDomain === null) {
            // Configuration option.
            if (defined('DECIBEL_CORE_SESSIONDOMAIN')
                && $this->isValidCookieDomain(DECIBEL_CORE_SESSIONDOMAIN)
            ) {
                $this->cookieDomain = DECIBEL_CORE_SESSIONDOMAIN;
                // Calculate root domain.
            } else {
                $request = DRequest::load();
                $this->cookieDomain = '.' . $this->getRootDomain($request);
            }
        }

        return $this->cookieDomain;
    }

    /**
     * Return the top level domain for the current request.
     *
     * For example, "www.google.com" will return "google.com"
     *
     * @param    DRequest $request
     *
     * @return    void
     */
    protected function getRootDomain(DRequest $request)
    {
        // Determine session cookie domain to enable cross-subdomain logins.
        $tlds = array(
            'aero', 'asia', 'biz', 'cat', 'com', 'coop', 'edu', 'gov', 'info',
            'int', 'jobs', 'mil', 'mobi', 'museum', 'name', 'net', 'org', 'pro',
            'tel', 'travel', 'local',
        );
        $sessionDomain = array();
        // Split the domain into parts. If only two parts exist, the full domain
        // is the top level domain. This will mostly happen in development
        // (e.g. website.test)
        $hostname = $request->getUrl()->getHostname();
        $domainParts = explode('.', $hostname);
        if (count($domainParts) == 2) {
            return $hostname;
        }
        // Otherwise find the standard TLD.
        foreach (array_reverse($domainParts) as $domainPart) {
            $sessionDomain[] = $domainPart;
            if (strlen($domainPart) != 2 && !in_array($domainPart, $tlds)) {
                break;
            }
        }

        return implode('.', array_reverse($sessionDomain));
    }

    /**
     * Tests whether the specified cookie domain can be used.
     *
     * @param    string $cookieDomain The domain to test.
     *
     * @return    bool
     */
    protected function isValidCookieDomain($cookieDomain)
    {
        if (empty($cookieDomain)
            || $cookieDomain{0} !== '.'
            || substr_count($cookieDomain, '.') <= 1
        ) {
            $valid = false;
        } else {
            $request = DRequest::load();
            $cookieDomainRegex = '/(^|\.)' . addcslashes(substr($cookieDomain, 1), '.') . '$/';
            $valid = (bool)preg_match($cookieDomainRegex, $request->getUrl()->getHostname());
        }

        return $valid;
    }
}
