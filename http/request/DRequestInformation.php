<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\http\request;

/**
 * Defines request information that can be injected into
 * a {@link DRequest} object instance.
 *
 * @note
 * Implementing classes are responsible for performing any required filtering
 * of information before returning it.
 *
 * @author    Timothy de Paris
 */
interface DRequestInformation
{
    /**
     * Returns arguments provided to the script if executed via CLI.
     *
     * @return    array
     */
    public function getArguments();

    /**
     * Returns the request body.
     *
     * @return    string
     */
    public function getBody();

    /**
     * Determines URL parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getUrlParameters();

    /**
     * Returns a list of request headers.
     *
     * @return    DRequestHeaders
     */
    public function getHeaders();

    /**
     * Determines the host for this request.
     *
     * @return    string
     */
    public function getHost();

    /**
     * Determines the request method.
     *
     * @return    string
     */
    public function getMethod();

    /**
     * Returns the port through which this request was served.
     *
     * @return    int
     */
    public function getPort();

    /**
     * Determines POST parameters for the request.
     *
     * @return    DRequestParameters
     */
    public function getPostParameters();

    /**
     * Determines the protocol for this request.
     *
     * @return    string
     */
    public function getProtocol();

    /**
     * Determines the referer of the request.
     *
     * @return    string
     */
    public function getReferer();

    /**
     * Returns a list of uploaded files.
     *
     * @return    DFileUploads    List of {@link DFileUpload} objects.
     */
    public function getUploadedFiles();

    /**
     * Determines the request URI.
     *
     * @note
     * This must exclude any preceding forward slash and query parameters.
     *
     * @return    string
     */
    public function getUri();
}
