<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http;

/**
 * Interface allowing an HTTP response to issue a redirect.
 *
 * @author    Timothy de Paris
 */
interface DRedirectableResponse
{
    /**
     * Returns the reason for the redirect being issued.
     *
     * @return    string
     */
    public function getRedirectReason();

    /**
     * Returns the URL to which the client should be redirected.
     *
     * @return    string
     */
    public function getRedirectUrl();
}
