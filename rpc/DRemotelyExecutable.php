<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\authorise\DUser;

/**
 *
 * @author        Timothy de Paris
 */
interface DRemotelyExecutable
{
    /**
     * Denotes a remote procedure returns JSON.
     *
     * @var        string
     */
    const RESULT_TYPE_JSON = 'application/json';
    /**
     * Denotes a remote procedure returns XML.
     *
     * @var        string
     */
    const RESULT_TYPE_XML = 'text/xml';
    /**
     * Denotes a remote procedure returns HTML.
     *
     * @var        string
     */
    const RESULT_TYPE_HTML = 'text/html';
    /**
     * Denotes a remote procedure returns plain text.
     *
     * @var        string
     */
    const RESULT_TYPE_TEXT = 'text/plain';
    /**
     * Denotes a remote procedure returns JavaScript code.
     *
     * @var        string
     */
    const RESULT_TYPE_JAVASCRIPT = 'application/javascript';

    /**
     * Determines if the specified user is authorised to execute
     * this remote functionality.
     *
     * @param    DUser $user The user to authorise.
     *
     * @return    bool    <code>true</code> if the user is able to execute
     *                    the procedure, <code>false</code> otherwise.
     */
    public function authorise(DUser $user);

    /**
     * Returns the relative URL at which this remote functionality
     * can be accessed.
     *
     * @return    string
     */
    public function getRelativeRemoteUrl();

    /**
     * Specifies the type of result returned to the client executing
     * this web service.
     *
     * This will ensure the correct MIME type is returned in the HTTP response
     * allowing the %Decibel JavaScript framework to correctly interpret
     * the result.
     *
     * @return    string    The result type, must be one of
     *                    {@link DRemotelyExecutable::RESULT_TYPE_JSON},
     *                    {@link DRemotelyExecutable::RESULT_TYPE_XML},
     *                    {@link DRemotelyExecutable::RESULT_TYPE_HTML},
     *                    {@link DRemotelyExecutable::RESULT_TYPE_TEXT} or
     *                    {@link DRemotelyExecutable::RESULT_TYPE_JAVASCRIPT}.
     */
    public function getResultType();

    /**
     * Returns the user session that will be used to authenticate access to the remote procedure.
     *
     * @return    DUserSession    The authenticated session, or <code>null</code> if no authenticated
     *                            session is available.
     */
    public function getUserSession();

    /**
     * Determines whether the procedure will log execution information
     * to the remote procedure log.
     *
     * @return    bool
     */
    public function log();

    /**
     * Determines if this is required to be executed through HTTPS.
     *
     * If this function returns true, requests made via HTTP will automatically
     * redirect to the HTTPS protocol.
     *
     * Note that it is the responsibility of this function to determine that
     * the server is capable of supporting an HTTPS request, otherwise requests
     * may fail.
     *
     * @return    bool
     */
    public function requiresHttps();
}
