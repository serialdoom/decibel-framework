<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

use app\decibel\debug\DErrorHandler;
use app\decibel\http\request\DRequest;
use app\decibel\http\request\DCliRequest;
use app\decibel\model\DBaseModel;
use app\decibel\utility\DBaseClass;
use app\decibel\utility\DInvalidSessionDataException;
use app\decibel\utility\DSingleton;
use app\decibel\utility\DSingletonClass;
use ArrayAccess;
use stdClass;

/**
 * Wrapper class for session management and access.
 *
 * @section       why Why Would I Use It?
 *
 * This class normalises access to session data and functionality providing
 * an extra level of security and error handling.
 *
 * @section       how How Do I Use It?
 *
 * This singleton class can be loaded as follows:
 *
 * @code
 * use app\decibel\utility\DSession;
 *
 * $session = DSession::load();
 * @endcode
 *
 * Once loaded, methods can be called as documented below.
 *
 * See the @ref utility_session Developer Guide for further information.
 *
 * @section       versioning Version Control
 *
 * @author        Timothy de Paris
 */
class DSession implements DSingleton, ArrayAccess
{
    use DBaseClass;
    use DSingletonClass;

    /**
     * 'Request' counter name.
     *
     * @var        string
     */
    const COUNTER_REQUEST = 'requestCount';

    /**
     * The name of the PHP session cookie.
     *
     * As defined by the <code>session.name</code> PHP configuration option.
     *
     * @var        string
     */
    protected $sessionName;

    /**
     * Information about the session cookie.
     *
     * @var        DSessionCookie
     */
    protected $sessionCookie;

    /**
     * Pointer to the PHP $_SESSION variable for internal use.
     *
     * @var        array
     */
    protected $sessionData;

    /**
     * Configures and starts the session.
     *
     * @return    DSession
     */
    protected function __construct()
    {
        // Determine configured session information.
        $this->sessionName = ini_get('session.name');
        $this->sessionCookie = new DSessionCookie();
        $this->sessionData =& $_SESSION;
        // No session available when running CLI.
        $request = DRequest::load();
        if ($request instanceof DCliRequest) {
            return;
        }
        // Ensure session IDs aren't exposed in URLs.
        // Should already be the default configuration
        // but set them again to be sure.
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_only_cookies', true);
        // Use HttpOnly cookies to provide some
        // protection against xss attacks.
        ini_set('session.cookie_httponly', true);
        // Use secure channel for cookies transmission
        // when page viewed over HTTPS.
        if ($request->isHttps()) {
            ini_set('session.cookie_secure', true);
        }
        // Set hashing algorithm.
        ini_set('session.hash_function', DECIBEL_CORE_SESSIONHASH);
        // Store session files in a folder for this user, to avoid potential
        // conflicts with other websites running on the same server.
        if (file_exists(SESSION_PATH)
            || mkdir(SESSION_PATH)
        ) {
            if (is_writable(SESSION_PATH)) {
                ini_set('session.save_path', SESSION_PATH);
            } else {
                $exception = new DInvalidSessionPathException(SESSION_PATH);
                DErrorHandler::throwException($exception);
            }
        }
        // If the session has already been started,
        // start it again automatically.
        if ($this->isStarted()) {
            $this->start();
        }
    }

    /**
     * Clears all parameters currently stored against the session.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * $session->clear();
     * @endcode
     *
     * @return    void
     */
    public function clear()
    {
        session_unset();
    }

    /**
     * Ends the session clearing all session data from the server
     * and removing the session cookie from the user's browser.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * if ($session->isStarted()) {
     *    $session->end();
     * }
     * @endcode
     *
     * @return    void
     */
    public function end()
    {
        $cookieDomain = $this->sessionCookie->getCookieDomain();
        if ($cookieDomain !== null) {
            session_destroy();
            setcookie($this->sessionName, null, 0, '/', $cookieDomain);
            // Remove any Decibel administration cookies.
            $request = DRequest::load();
            setcookie('app-decibel-regional-editinglanguage', null, 0, '/', $request->getUrl()->getHostname());
        }
    }

    /**
     * Returns a specified session parameter.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * debug($session->get('myData'));
     * @endcode
     *
     * @note
     * This method returns a pointer to the specified parameter in the session,
     * therefore manipulation of the returned variable will result in changing
     * the session data itself.
     *
     * @param    string $name         Name of the parameter to return.
     * @param    mixed  $default      The default return value, if the parameter
     *                                cannot be found. If used, this value will
     *                                also be stored in the session.
     *
     * @return    mixed    A pointer to the parameter value.
     * @throws    DInvalidSessionDataException    If the provided default value
     *                                            cannot be stored in the session.
     */
    public function &get($name, $default = null)
    {
        $location = $this->getDataLocation($name, true, $default);

        return $location->parent[ $location->key ];
    }

    /**
     * Retrieves the data location for a specified name.
     *
     * @param    string $name         Data location name.
     * @param    bool   $create       Whether to create the data location,
     *                                if it doesn't already exist.
     * @param    mixed  $default      If the location is created, this default
     *                                value will be applied.
     *
     * @return    stdClass    An object containing a pointer to the parent data
     *                        location (<code>stdClass::parent</code>) and the
     *                        name of the data location key (<code>stdClass::key</code>),
     *                        or <code>null</code> if the data location doesn't
     *                        exist and creation was not requested.
     * @throws    DInvalidSessionDataException    If the provided default value
     *                                            cannot be stored in the session.
     */
    private function getDataLocation($name, $create = false, $default = null)
    {
        $location = new stdClass();
        $data =& $this->sessionData;
        foreach (explode('-', $name) as $key) {
            if (!isset($data[ $key ])) {
                if ($create) {
                    $this->validateSessionData($default);
                    $data[ $key ] = $default;
                } else {
                    return null;
                }
            }
            $location->parent =& $data;
            $location->key = $key;
            $data =& $data[ $key ];
        }

        return $location;
    }

    /**
     * Returns the ID of the session.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * debug($session->getId());
     * @endcode
     *
     * Will output the session ID such as <code>bfu58d6hl0ac9tim78mdcokb50</code>
     *
     * @return    string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Returns the session name.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * debug($session->getName());
     * @endcode
     *
     * Will output the session name such as <code>PHPSESSID</code>
     *
     * @return    string
     */
    public function getName()
    {
        return $this->sessionName;
    }

    /**
     * Returns the number of this request within the current session.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * debug('This is request number ' . $session->getRequestCount());
     * @endcode
     *
     * @return    int
     */
    public function getRequestCount()
    {
        return $this->sessionData[ self::class ][ self::COUNTER_REQUEST ];
    }

    /**
     * Determines if this is the first request of a new session.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * if ($session->isNewSession()) {
     *    debug('First page view!');
     * }
     * @endcode
     *
     * @return    bool
     */
    public function isNewSession()
    {
        return isset($this->sessionData[ self::class ])
        && $this->sessionData[ self::class ][ self::COUNTER_REQUEST ] === 1;
    }

    /**
     * Determines if the session has been started.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * if (!$session->isStarted()) {
     *    $session->start();
     * }
     * @endcode
     *
     * @return    bool
     */
    public function isStarted()
    {
        return isset($_COOKIE[ $this->sessionName ]);
    }

    /**
     * Reduce the request count for the session by the provided amount.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * debug($session->reduceRequestCount());
     * @endcode
     *
     * @param    int $amount The number by which to reduce the session request count.
     *
     * @return    int        The new session request count.
     */
    public function reduceRequestCount($amount = 1)
    {
        $this->sessionData[ self::class ][ self::COUNTER_REQUEST ] -= $amount;

        return $this->sessionData[ self::class ][ self::COUNTER_REQUEST ];
    }

    /**
     * Replaces the current session ID with a new one,
     * while maintaining the session data.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * $session->regenerateId();
     * @endcode
     *
     * @return    bool    <code>true</code> if the ID was successfully replaced,
     *                    <code>false</code> otherwise.
     */
    public function regenerateId()
    {
        return session_regenerate_id(true);
    }

    /**
     * Stores a value in the session.
     *
     * @note
     * If the session has not yet been started, it will be started
     * before the data is stored.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * $session->set('myData', 123);
     * @endcode
     *
     * @param    string $name  Name of the parameter.
     * @param    mixed  $value The value to store in the session.
     *
     * @return    void
     * @throws    DInvalidSessionDataException    If the provided value cannot
     *                                            be stored in the session
     */
    public function set($name, $value)
    {
        $this->validateSessionData($value);
        if (!$this->isStarted()) {
            $this->start();
        }
        $location = $this->getDataLocation($name, true);
        $location->parent[ $location->key ] = $value;
    }

    /**
     * Starts the session.
     *
     * @code
     * use app\decibel\utility\DSession;
     *
     * $session = DSession::load();
     * if (!$session->isStarted()) {
     *    $session->start();
     * }
     * @endcode
     *
     * @return    bool    <code>true</code> if the session was able to be started,
     *                    <code>false</code> if not.
     */
    public function start()
    {
        $cookieDomain = $this->sessionCookie->getCookieDomain();
        if ($cookieDomain === null) {
            return false;
        }
        // Begin session.
        if (session_id() === ''
            && isset($_SERVER['HTTP_HOST'])
        ) {
            session_set_cookie_params(0, '/', $cookieDomain);
            // Start the session.
            if (session_start()) {
                $_COOKIE[ $this->sessionName ] = session_id();
                // Update the request count for this session.
                set_default($this->sessionData[ self::class ][ self::COUNTER_REQUEST ], 0);
                ++$this->sessionData[ self::class ][ self::COUNTER_REQUEST ];

                return true;
            }
        }

        return false;
    }

    /**
     * Test the provided value to determine if it can be stored in the session.
     *
     * @param    mixed $value The value to test.
     *
     * @throws    DInvalidSessionDataException    If the provided value cannot
     *                                            be stored in the session.
     * @return    void
     */
    protected function validateSessionData($value)
    {
        if (is_resource($value)) {
            throw new DInvalidSessionDataException('Cannot store resources in the session.');
        }
        if (is_object($value)
            && $value instanceof DBaseModel
        ) {
            throw new DInvalidSessionDataException('Cannot store model instances in the session. Store the instance ID instead.');
        }
    }

    /**
     * Allows a session parameter to be set using array syntax.
     *
     * @param    string $name  Name of the parameter.
     * @param    mixed  $value Parameter value.
     *
     * @return    void
     * @throws    DInvalidSessionDataException    If the provided value cannot
     *                                            be stored in the session
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Allows checking for the existence of a session parameter using array syntax.
     *
     * @param    string $name Name of the parameter.
     *
     * @return    void
     */
    public function offsetExists($name)
    {
        return ($this->getDataLocation($name) !== null);
    }

    /**
     * Allows a session parameter to be unset using array syntax.
     *
     * @param    string $name Name of the parameter.
     *
     * @return    void
     */
    public function offsetUnset($name)
    {
        $location = $this->getDataLocation($name);
        if ($location !== null) {
            unset($location->parent[ $location->key ]);
        }
    }

    /**
     * Allows a session parameter to be returned using array syntax.
     *
     * @param    string $name Name of the parameter.
     *
     * @return    mixed    The parameter value.
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }
}
