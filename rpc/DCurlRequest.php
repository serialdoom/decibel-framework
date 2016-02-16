<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\debug\DException;
use app\decibel\rpc\auditing\DOutboundRequestRecord;
use app\decibel\rpc\debug\DDuplicateHeaderException;
use app\decibel\rpc\debug\DInvalidRemoteProcedureCallException;
use app\decibel\rpc\debug\DRemoteServerException;
use app\decibel\rpc\debug\DRemoteServerRedirectException;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\utility\DJson;
use Exception;

/**
 * Wrapper class for calling a executing a curl request.
 *
 * A fluent interface (see http://en.wikipedia.org/wiki/Fluent_interface)
 * is provided to allow chained function calls to build the request,
 * for example:
 *
 * @code
 * use app\decibel\rpc\DCurlRequest;
 *
 * $mimeType = null;
 * $response = DCurlRequest::create('http://www.example.com/endpoint')
 *        ->setCredentials('username', 'password')
 *        ->setParameter('token', 'abcd1234')
 *        ->execute($mimeType);
 * @endcode
 *
 * @author    Timothy de Paris
 */
class DCurlRequest
{
    /**
     * Whether the request will be audited.
     *
     * @var        DCurlRequestAuditOptions
     */
    protected $auditOptions;
    /**
     * Number of seconds the connection will be attempted for.
     *
     * @var        int
     */
    protected $connectTimeout = 2;
    /**
     * Custom cURL options for the request.
     *
     * @var        array
     */
    protected $curlOptions = array();
    /**
     * URL to request.
     *
     * @var        string
     */
    protected $url;
    /**
     * The username that will be used to authenticate against the remote server.
     *
     * @var        string
     */
    protected $username = null;
    /**
     * The password that will be used to authenticate against the remote server.
     *
     * @var        string
     */
    protected $password = null;
    /**
     * Parameters to be passed to the remote procedure.
     *
     * @var        string
     */
    protected $parameters = array();
    /**
     * POST body content for the request.
     *
     * @var        string
     */
    protected $body;
    /**
     * Headers to be sent with the request.
     *
     * @var        array
     */
    protected $headers = array();
    /**
     * CURL error messages.
     *
     * @var        array
     */
    private static $errorMessages = array(
        CURLE_COULDNT_RESOLVE_HOST => 'Unable to resolve hostname of the remote server.',
        CURLE_COULDNT_CONNECT      => 'Unable to connect to the remote server.',
        CURLE_OPERATION_TIMEOUTED  => 'Connection to remote server timed out. Check that the URL is valid or use <code>app\decibel\rpc\DCurlRequest::setConnectTimeout()</code> to increase the connection timeout.',
    );
    /**
     * HTTP Error code Exceptions
     *
     * @var        array
     */
    private static $httpExceptions = array(
        300 => DRemoteServerRedirectException::class,
        301 => DRemoteServerRedirectException::class,
        302 => DRemoteServerRedirectException::class,
        303 => DRemoteServerRedirectException::class,
        304 => 'app\\decibel\\rpc\\debug\\DRemoteServerNotFoundException',
        400 => 'app\\decibel\\rpc\\debug\\DRemoteServerBadRequestException',
        401 => 'app\\decibel\\rpc\\debug\\DRemoteServerUnauthorisedException',
        404 => 'app\\decibel\\rpc\\debug\\DRemoteServerNotFoundException',
        500 => 'app\\decibel\\rpc\\debug\\DRemoteServerServiceUnavailableException',
        503 => 'app\\decibel\\rpc\\debug\\DRemoteServerServiceUnavailableException',
    );

    /**
     * Creates a new curl request.
     *
     * @param    string $url URL to request.
     *
     * @return    DCurlRequest
     */
    protected function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Creates a new curl request.
     *
     * @param    string $url URL to request.
     *
     * @return    static
     */
    public static function create($url)
    {
        return new static($url);
    }

    /**
     * Audits an executed request, if auditing is enabled.
     *
     * @param    string                               $response  The response returned by the remote server.
     * @param    DInvalidRemoteProcedureCallException $exception The exception
     *                                                           triggered when executing the request, if any.
     *
     * @return    bool
     */
    protected function auditRequest($response,
                                    DInvalidRemoteProcedureCallException $exception = null)
    {
        if ($this->auditOptions === null) {
            return false;
        }
        // Request is successful if no exception passed.
        if ($exception === null) {
            // Don't log successful requests if not required.
            if (!$this->auditOptions->getLogSuccessful()) {
                return false;
            }
            $statusCode = 200;
            $error = null;
            // Otherwise extract the error information.
        } else {
            $statusCode = $exception->getHttpCode();
            $error = $exception->getMessage();
        }
        $headers = array();
        foreach ($this->headers as $header => $value) {
            $headers[] = "{$header}: {$value}";
        }
        // Mask content if required.
        $maskedPostBody = $this->auditOptions->applyPostBodyMask($this->body);
        $maskedResponse = $this->auditOptions->applyResponseMask($response);
        DOutboundRequestRecord::log(array(
                                        'url'        => $this->url,
                                        'parameters' => http_build_query($this->parameters),
                                        'headers'    => implode("\n", $headers),
                                        'postBody'   => $maskedPostBody,
                                        'username'   => $this->username,
                                        'password'   => (bool)$this->password,
                                        'response'   => $maskedResponse,
                                        'statusCode' => $statusCode,
                                        'error'      => $error,
                                    ));

        return true;
    }

    /**
     * Sets whether the request will be audited.
     *
     * By default, requests will not be audited.
     *
     * @param    DCurlRequestAuditOptions $options Auditing options.
     *
     * @return    static
     */
    public function enableAuditing(DCurlRequestAuditOptions $options = null)
    {
        $this->auditOptions = $options;

        return $this;
    }

    /**
     * Executes the curl request on the remote server.
     *
     * @param    string $mimeType     Pointer in which the mime type returned
     *                                by the remote server will be stored.
     *
     * @return    mixed    The result of the curl request.
     * @throws    DInvalidRemoteProcedureCallException    If the remote server is
     *                                                    unable to execute the
     *                                                    remote procedure.
     * @throws    DException    If the remote server returns a JSON encoded
     *                        {@link app::decibel::debug::DException DException}
     *                        object, this will be decoded and thrown
     *                        by this method.
     */
    public function execute(&$mimeType = null)
    {
        // Prepare and execute the request.
        $handle = $this->prepareHandle();
        $resultString = curl_exec($handle);
        // Handle CURL error codes.
        try {
            $errorCode = curl_errno($handle);
            $this->handleCurlErrorCode($errorCode, $handle);
        } catch (DInvalidRemoteProcedureCallException $exception) {
            $this->auditRequest('', $exception);
            $exception->regenerateBacktrace();
            throw $exception;
        }
        // Decode the result if the application/json mime type is returned.
        $contentType = explode(';', curl_getinfo($handle, CURLINFO_CONTENT_TYPE));
        $mimeType = trim($contentType[0]);
        if ($mimeType === DRemoteProcedure::RESULT_TYPE_JSON) {
            $result = DJson::decode($resultString);
        } else {
            $result =& $resultString;
        }
        // Handle HTTP error codes.
        try {
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $this->handleHttpCode($httpCode, $result);
        } catch (DInvalidRemoteProcedureCallException $exception) {
            $this->auditRequest($resultString, $exception);
            $exception->regenerateBacktrace();
            throw $exception;
        }
        // Close the cURL handle.
        curl_close($handle);
        // Audit the successful request.
        $this->auditRequest($resultString);
        if ($result instanceof Exception) {
            throw $result;
        }

        return $result;
    }

    /**
     * Returns an array of curl options neccessary for execution of this
     * curl request, based on options set during it's creation.
     *
     * @return    array
     */
    protected function getCurlOptions()
    {
        $curlOptions = $this->curlOptions;
        if ($this->body) {
            $curlOptions[ CURLOPT_CUSTOMREQUEST ] = 'POST';
            $curlOptions[ CURLOPT_POSTFIELDS ] = $this->body;
        }
        foreach ($this->headers as $header => $value) {
            $curlOptions[ CURLOPT_HTTPHEADER ][] = "{$header}: {$value}";
        }
        // Set connection timeout.
        $curlOptions[ CURLOPT_CONNECTTIMEOUT ] = $this->connectTimeout;

        return $curlOptions;
    }

    /**
     * Handles CURL error codes returned while executing a remote procedure.
     *
     * @param    int      $errorCode      Returned CURL error code.
     * @param    resource $handle         The CURL handle, in case further
     *                                    information is required.
     *
     * @return    void
     * @throws    DInvalidRemoteProcedureCallException    If the CURL error code
     *                                                    is anything other than
     *                                                    <code>CURLE_OK</code>
     */
    protected function handleCurlErrorCode($errorCode, $handle)
    {
        if ($errorCode === CURLE_OK) {
            return;
        }
        if (array_key_exists($errorCode, DCurlRequest::$errorMessages)) {
            $errorMessage = DCurlRequest::$errorMessages[ $errorCode ];
        } else {
            $errorMessage = 'Unknown error returned: ' . curl_error($handle);
        }
        throw new DRemoteServerException(
            $this->url,
            $errorMessage
        );
    }

    /**
     * Handles HTTP codes returned from a remote server following execution
     * of a remote procedure.
     *
     * @param    int   $httpCode Returned HTTP code.
     * @param    mixed $result   Content returned by the remote procedure.
     *
     * @return    void
     * @throws    DInvalidRemoteProcedureCallException    If the HTTP code is anything
     *                                                    other than <code>200</code>.
     */
    protected function handleHttpCode($httpCode, &$result)
    {
        $location = $this->url;
        if ($httpCode === 200) {
            return;
        }
        if (array_key_exists($httpCode, DCurlRequest::$httpExceptions)) {
            $exception = DCurlRequest::$httpExceptions[ $httpCode ];
            throw new $exception($location, $httpCode, $result);
        } else {
            throw new DRemoteServerException($location, $httpCode, $result);
        }
    }

    /**
     * Prepares a cURL handle that can be used to execute this request.
     *
     * @return    resource
     */
    protected function prepareHandle()
    {
        // Set up the cURL handle.
        $handle = curl_init($this->url);
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        if ($this->parameters) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($this->parameters));
        }
        // Set up authentication.
        if ($this->username && $this->password) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($handle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }
        // Set any provided custom options.
        $curlOptions = $this->getCurlOptions();
        if ($curlOptions) {
            curl_setopt_array($handle, $curlOptions);
        }

        return $handle;
    }

    /**
     * Sets the amount of time for which the request will attempt
     * to connect to the remote server.
     *
     * @note
     * If this method is not called, a connection timeout of 2 seconds
     * will be used.
     *
     * @param    int $seconds         Number of seconds the connection will
     *                                be attempted for.
     *
     * @return    static
     */
    public function setConnectTimeout($seconds)
    {
        $this->connectTimeout = $seconds;

        return $this;
    }

    /**
     * Sets credentials that will be used to authenticate against
     * the remote server.
     *
     * @note
     * If this method is not called, no credentials will be provided
     * to the remote server.
     *
     * @param    string $username The username.
     * @param    string $password The password.
     *
     * @return    static
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * Allows custom options to be set on the cURL handle.
     *
     * @param    array $options Key/value option pairs.
     *
     * @return    static
     */
    public function setCurlOptions(array $options)
    {
        $this->curlOptions += $options;

        return $this;
    }

    /**
     * Sets a header to be sent with the request.
     *
     * @param    string $header The header to set.
     * @param    string $value  Value for the header.
     *
     * @return    static
     * @throws    DDuplicateHeaderException    If the header has already been set.
     */
    public function setHeader($header, $value)
    {
        if (array_key_exists($header, $this->headers)) {
            throw new DDuplicateHeaderException($header);
        }
        $this->headers[ $header ] = $value;

        return $this;
    }

    /**
     * Adds a parameter to be passed to the curl request.
     *
     * @param    string $name  The parameter name.
     * @param    mixed  $value The parameter value.
     *
     * @return    static
     */
    public function setParameter($name, $value)
    {
        $this->parameters[ $name ] = $value;

        return $this;
    }

    /**
     * Adds multiple parameters to be passed to the curl request.
     *
     * @param    array $parameters Key/value parameter pairs.
     *
     * @return    static
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }

        return $this;
    }

    /**
     * Sets the content to be sent as the body of this request.
     *
     * @note
     * If a post body is set, any parameters added via the
     * {@link DCurlRequest::setParameter()} or
     * {@link DCurlRequest::setParameters()} methods will be
     * sent as GET parameters.
     *
     * @warning
     * This will override the "Content-Type" and "Content-Length" headers
     * if they have already been set using {@link DCurlRequest::setHeader()}
     *
     * @param    string $data         The data to be posted.
     * @param    string $mimeType     The mime type of the posted data, to be added
     *                                to the request headers.
     * @param    string $charset      The charcter set of the posted data, to be added
     *                                to the request headers.
     *
     * @return    static
     */
    public function setPostBody($data, $mimeType = 'application/json', $charset = 'utf-8')
    {
        $this->body = $data;
        // Set required headers.
        $this->headers['Content-Type'] = "{$mimeType};charset={$charset}";
        $this->headers['Content-Length'] = strlen($data);

        return $this;
    }
}
