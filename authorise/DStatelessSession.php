<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\authorise;

use app\decibel\utility\DBaseClass;

/**
 * Provides functionality to generate and manage authentication tokens for authenticated sessions.
 *
 * @author        Timothy de Paris
 */
class DStatelessSession implements DUserSession
{
    use DBaseClass;
    /**
     * The user associated with this session.
     *
     * @var        DUser
     */
    protected $user;
    /**
     * The token identifying this session.
     *
     * @var        string
     */
    protected $token;
    /**
     * Timestamp representing the time at which this session will expire.
     *
     * @var        int
     */
    protected $expiry;
    /**
     * Timestamp representing the last time the session expiry was updated in the database.
     *
     * @var        int
     */
    protected $lastUpdated;
    /**
     * Reference to the persisted session token information.
     *
     * @var        DSessionToken
     */
    private $sessionToken;
    /**
     * Cache for loaded sessions to avoid multiple database queries.
     *
     * @var    array
     */
    private static $sessionCache = array();

    /**
     * Creates a new {@link DSession} instance.
     *
     * @param    DSessionToken $sessionToken The persisted session token information.
     *
     * @return    static
     */
    protected function __construct(DSessionToken $sessionToken)
    {
        $this->user = $sessionToken->getUser();
        $this->token = $sessionToken->getToken();
        $this->expiry = $sessionToken->getExpiryTime();
        $this->lastUpdated = $sessionToken->getLastUpdated();
        $this->sessionToken = $sessionToken;
    }

    /**
     * Registers a new session for the provided user.
     *
     * @note
     * It is assumed that the provided user has been successfully authenticated
     * before this method is called.
     *
     * @param    DUser $user
     *
     * @return    static
     */
    public static function create(DUser $user)
    {
        // Generate a new session token.
        $token = static::generateToken();
        // Calculate the expiry time for this new session.
        $expiry = static::getExpiryForUser($user);
        $sessionToken = DSessionToken::create();
        $sessionToken->setFieldValue(DSessionToken::FIELD_USER, $user);
        $sessionToken->setFieldValue(DSessionToken::FIELD_TOKEN, $token);
        $sessionToken->setFieldValue(DSessionToken::FIELD_EXPIRY, $expiry);

        return new static($sessionToken);
    }

    /**
     * Determines the time at which a new session should expire for the provided user.
     *
     * @param    DUser $user
     *
     * @return    int
     */
    protected static function getExpiryForUser(DUser $user)
    {
        return time() + ($user->getSessionExpiryTime() * 60);
    }

    /**
     * Loads the session with the specified token.
     *
     * @param    string $token
     *
     * @return    static
     */
    public static function getWithToken($token)
    {
        if (!array_key_exists($token, self::$sessionCache)) {
            $sessionToken = DSessionToken::search()
                                         ->filterByField(DSessionToken::FIELD_TOKEN, $token)
                                         ->limitTo(1)
                                         ->getObject();
            if ($sessionToken) {
                self::$sessionCache[ $token ] = new static($sessionToken);
            } else {
                self::$sessionCache[ $token ] = null;
            }
        }

        return self::$sessionCache[ $token ];
    }

    /**
     * Generates a token for an authenticated session.
     *
     * @return    string
     */
    protected static function generateToken()
    {
        $salt = base64_encode(mcrypt_create_iv(20, MCRYPT_DEV_URANDOM));

        return sha1(time() . $salt);
    }

    /**
     * Returns the expiry time of this session.
     *
     * @return    int
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Returns the token associated with this session.
     *
     * @return    string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the user associated with this session.
     *
     * @return    DUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Invalidates the token, ending the session for the associated user.
     *
     * @return    void
     */
    public function invalidate()
    {
        $this->sessionToken->delete($this->user);
        $this->user->logout();
    }

    /**
     * Determines if the login time in the database should be updated.
     *
     * @return    bool
     */
    protected function isUpdateRequired()
    {
        $tolerance = time() - DECIBEL_CORE_EXPIRYTOLERANCE;

        return ($tolerance > $this->expiry);
    }

    /**
     * Updates the expiry time on the session.
     *
     * @return    void
     */
    public function update()
    {
        if ($this->isUpdateRequired()) {
            $this->expiry = static::getExpiryForUser($this->user);
            $this->sessionToken->setFieldValue(DSessionToken::FIELD_EXPIRY, $this->expiry);
            $this->sessionToken->save($this->user);
        }
    }
}
