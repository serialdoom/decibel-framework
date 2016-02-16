<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\cookie;

use app\decibel\http\debug\DInvalidCookieException;
use app\decibel\http\debug\DUnknownCookieException;
use app\decibel\http\request\DRequest;

/**
 * Provides a fluent interface for the manipulation of cookies.
 *
 * @section       why Why Would I Use It?
 *
 * This class abstracts functionality for setting and reading cookies.
 * It implements additional checks to improve the security of cookie related
 * functionality, and should therefore be used instead of native
 * PHP functionality.
 *
 * @section       how How Do I Use It?
 *
 * The class can be used to access values of cookies sent by the browser,
 * or to set new cookies or update existing cookies on the browser.
 *
 * See the examples below for further details.
 *
 * @subsection    example Example
 *
 * The following example lists all cookies sent by the browser:
 *
 * @code
 * use app\decibel\http\cookie\DCookie;
 *
 * foreach (DCookie::getCookies() as $name => $cookie) {
 *    debug($name . ' = ' . $cookie->getValue());
 * }
 * @endcode
 *
 * This example retrieves the value of a cookie sent by the browser:
 *
 * @code
 * use app\decibel\http\cookie\DCookie;
 * use app\decibel\http\debug\DUnknownCookieException;
 *
 * try {
 *  $value = DCookie::create('TestCookie')
 *    ->getValue();
 * } catch (DUnknownCookieException $e) {
 * }
 * @endcode
 *
 * @warning
 * The {@link DCookie::create()} method will throw a {@link DUnknownCookieException}
 * in the above example if no cookie exists with the name 'TestCookie', so developers
 * should always enclose this functionality with an appropriate
 * <code>try...catch</code> block.
 *
 * This example sends a new cookie to the browser:
 *
 * @code
 * use app\decibel\http\cookie\DCookie;
 *
 * $result = DCookie::create('TestCookie', 'TestValue')
 *    ->setExpiry('+1 day')
 *    ->setAvailability('mydomain.com')
 *    ->setSecure()
 *    ->send();
 * @endcode
 *
 * @note
 * If the {@link DCookie::setAvailability()} method is not called, the cookie
 * will be available on all paths of the current domain and it's sub-domains.
 *
 * The final example shows how to delete a cookie from the browser.
 *
 * @code
 * use app\decibel\http\cookie\DCookie;
 *
 * $result = DCookie::create('TestCookie')
 *    ->setAvailability('mydomain.com')
 *    ->delete();
 * @endcode
 *
 * @note
 * It is important that the domain and path of the cookie match that
 * of the cookie in the browser that requires deletion. As this information
 * is not provided by the browser, it must be set against the cookie before
 * calling the {@link DCookie::delete()} method.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DCookie
{
    /**
     * Name of the cookie.
     *
     * @var        string
     */
    protected $name;
    /**
     * Value of the cookie.
     *
     * @var        string
     */
    protected $value = null;
    /**
     * The cookie expiry time.
     *
     * @var        int
     */
    protected $expiry = null;
    /**
     * The domain on which the cookie is available.
     *
     * @var        string
     */
    protected $domain;
    /**
     * The path on which the cookie is available.
     *
     * @var        string
     */
    protected $path = '/';
    /**
     * Whether the cookie may only be accessed
     * through the HTTP protocol
     *
     * @var        bool
     */
    protected $httpOnly = false;
    /**
     * Whether the cookie may only be transmitted over
     * a secure HTTPS connection.
     *
     * @var        bool
     */
    protected $secure = false;

    /**
     * Creates a DCookie object.
     *
     * @param    string $name     Name of the cookie.
     * @param    string $value    The cookie value. If not provided, the value
     *                            will be loaded from cookies sent by the browser.
     *
     * @return    static
     * @throws    DUnknownCookieException    If no value was provided and no cookie
     *                                    with the provided name was sent
     *                                    by the browser.
     */
    public function __construct($name, $value = null)
    {
        $this->name = $name;
        // Load value from browser.
        if ($value === null) {
            $value = filter_input(INPUT_COOKIE, $name);
            if ($value === null) {
                throw new DUnknownCookieException($name);
            }
            // Assign provided value.
        } else {
            $this->value = $value;
        }
    }

    /**
     * Creates a DCookie object.
     *
     * @param    string $name     Name of the cookie.
     * @param    string $value    The cookie value. If not provided, the value
     *                            will be loaded from cookies sent by the browser.
     *
     * @return    static
     * @throws    DInvalidCookieException    If no value was provided and no cookie
     *                                    with the provided name was sent
     *                                    by the browser.
     */
    public static function create($name, $value = null)
    {
        return new static($name, $value);
    }

    /**
     * Instructs the browser to delete the cookie.
     *
     * @return    bool    Whether the deletion request was able to be sent
     *                    to the browser.    This does not indicate that the browser
     *                    actually deleted the cookie.
     */
    public function delete()
    {
        unset($_COOKIE[ $this->name ]);

        return setcookie($this->name, '', 1, $this->path,
                         $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     * Returns a list containing all cookies currently set.
     *
     * @return    array    List of {@link DCookie} objects.
     */
    public static function getCookies()
    {
        $cookies = array();
        foreach (filter_input(INPUT_COOKIE) as $name => $value) {
            $cookies[ $name ] = DCookie::create($name, $value);
        }

        return $cookies;
    }

    /**
     * Returns the domain on which the cookie is available.
     *
     * @return    string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Returns the expiry time for this cookie.
     *
     * @return    int        Timestamp representing the expiry time,
     *                    or <code>null</code> if this is a session cookie.
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Returns the name of the cookie.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the path on which the cookie is available.
     *
     * @return    string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the current value of the cookie.
     *
     * @return    string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Determines whether the cookie may only be accessed
     * through the HTTP protocol
     *
     * @return    bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Determines whether the cookie may only be transmitted over
     * a secure HTTPS connection.
     *
     * @return    bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Sends the cookie to the browser.
     *
     * @return    bool    Whether the cookie was able to be sent to the browser.
     *                    This does not indicate acceptance by the user.
     */
    public function send()
    {
        return setcookie($this->name, $this->value, $this->expiry,
                         $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     * Sets the availability parameters for the cookie.
     *
     * @param    string $domain Domain on which the cookie will be available.
     * @param    string $path   Path on which the cookie will be available.
     *
     * @return    static
     */
    public function setAvailability($domain, $path = '/')
    {
        $this->domain = $domain;
        $this->path = $path;

        return $this;
    }

    /**
     * Sets a new expiry for the cookie.
     *
     * @param    int $expiry      UNIX timestamp denoting the time at which the
     *                            cookie will expire, any string value accepted
     *                            by the PHP <code>strtotime</code> function, or
     *                            <code>null</code> to make this a session cookie.
     *
     * @return    static
     * @throws    DInvalidCookieException    If the provided expiry parameter is
     *                                    unable to be parsed.
     */
    public function setExpiry($expiry = null)
    {
        if (is_string($expiry) && !is_numeric($expiry)) {
            $parsedExpiry = strtotime($expiry);
            if ($parsedExpiry === false) {
                throw new DInvalidCookieException("Provided expiry value '{$expiry}' is not valid.");
            } else {
                $expiry = $parsedExpiry;
            }
        }
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Sets the HTTP only status of the cookie.
     *
     * HTTP only cookies can only be accessed through the HTTPS protocol.
     * This means the cookie will not be available to JavaScript or other
     * client-side scripting languages.
     *
     * @param    bool $httpOnly       Whether the cookie may only be accessed
     *                                through the HTTP protocol
     *
     * @return    static
     */
    public function setHttpOnly($httpOnly = true)
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * Sets the secure status of the cookie.
     *
     * Secure cookies may only be transmitted over a secure HTTPS connection.
     *
     * @param    bool $secure     Whether the cookie may only be transmitted
     *                            over a secure HTTPS connection.
     *
     * @return    static
     * @throws    DInvalidCookieException    If the cookie is set as secure on
     *                                    a non-secure connection.
     */
    public function setSecure($secure = true)
    {
        $request = DRequest::load();
        if ($secure && !$request->isHttps()) {
            throw new DInvalidCookieException('Unable to create a secure cookie on a non-HTTPS connection.');
        }
        $this->secure = $secure;

        return $this;
    }
}
