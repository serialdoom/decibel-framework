<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\security;

use app\decibel\utility\DSession;

/**
 * Contains functionality to generate and validate CSRF tokens for forms.
 *
 * @author    Timothy de Paris
 */
class DCsrfToken
{
    /**
     * Session key for storing the token.
     *
     * @var        string
     */
    const SESSION_KEY = 'app\\decibel\\security\\DCsrfToken-token';

    /**
     * Checks that the provided token is equal to the generated
     * CSRF token for this session.
     *
     * @note
     * If no token has been generated, this method will always
     * return <code>false</code>.
     *
     * @param    string $token
     *
     * @return    bool
     */
    public static function checkToken($token)
    {
        $session = DSession::load();

        return ($token === $session[ self::SESSION_KEY ]);
    }

    /**
     * Returns a CSRF token for the current session.
     *
     * @note
     * This will also generate the token if this is the first request
     * for a token in the current session.
     *
     * @return    string
     */
    public static function getToken()
    {
        $session = DSession::load();
        if (!isset($session[ self::SESSION_KEY ])) {
            // Generate a random token.
            $token = base64_encode(
                mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)
            );
            $session[ self::SESSION_KEY ] = $token;
        }

        return $session[ self::SESSION_KEY ];
    }
}
