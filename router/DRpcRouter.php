<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\router;

use app\decibel\authorise\DGuestUser;
use app\decibel\authorise\DUser;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DException;
use app\decibel\http\DHttpResponse;
use app\decibel\http\DOk;
use app\decibel\http\DPermanentRedirect;
use app\decibel\http\error\DBadRequest;
use app\decibel\http\error\DInternalServerError;
use app\decibel\http\error\DNotFound;
use app\decibel\http\error\DUnauthorised;
use app\decibel\http\request\DEntityBodyRequest;
use app\decibel\http\request\DRequest;
use app\decibel\regional\DLabel;
use app\decibel\regional\DLanguage;
use app\decibel\registry\DClassQuery;
use app\decibel\router\DRouter;
use app\decibel\rpc\auditing\DRpcLog;
use app\decibel\rpc\DRemotelyExecutable;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\stream\DTextStream;
use app\decibel\utility\DJson;

/**
 * Translates remote procedure calls into an action to be performed by the system.
 *
 * @author        Timothy de Paris
 */
class DRpcRouter extends DRouter
{
    /**
     * The remote procedure being executed by the router.
     *
     * @var        DRemoteProcedure
     */
    protected $rpc;

    /**
     * Determines if a user has been authorised to execute this remote procedure.
     *
     * @param    DUser               $user            The authenticated user.
     * @param    DRemotelyExecutable $rpc             The executed remote procedure.
     * @param                        DUnauthorised    If authorisation is required to execute this RPC.
     *
     * @return    void
     */
    protected function checkAuthorisation(DUser $user, DRemotelyExecutable $rpc)
    {
        if (!$rpc->authorise($user)) {
            throw new DUnauthorised();
        }
    }

    /**
     * Checks if HTTPS is required and if not currently accessed via HTTPS
     * issues a redirect.
     *
     * @param    DRemotelyExecutable $rpc
     * @param    DRequest            $request
     *
     * @return    void
     * @throws    DPermanentRedirect    If the requested page must be accessed
     *                                via the HTTPS protocol.
     */
    protected static function checkHttps(DRemotelyExecutable $rpc, DRequest $request)
    {
        if ($rpc->requiresHttps()
            && !$request->isHttps()
        ) {
            $url = $request->getUrl();
            $uri = $url->getUri();
            $hostname = $url->getHostname();
            throw new DPermanentRedirect(
                "https://{$hostname}/{$uri}",
                new DLabel(self::class, 'errorHttps')
            );
        }
    }

    /**
     * Defines the pattern that will be used to determine if a request
     * can be handled by this router.
     *
     * @return    string
     */
    public static function getPattern()
    {
        return '/^(remote\/decibel\/rpc\/DHandshake$|'
        . addcslashes(rtrim(DECIBEL_CORE_RPCPATH, '/'), '/')
        . '(\/.+$))/';
    }

    /**
     * Returns the authenticated user for this request.
     *
     * @return    DUser
     */
    public function getUser()
    {
        $session = $this->rpc->getUserSession();
        if ($session !== null) {
            $user = $session->getUser();
        } else {
            $user = DGuestUser::create();
        }

        return $user;
    }

    /**
     * Returns the remotely executable object that matches the provided URI.
     *
     * @param    string   $uri     The URI to match.
     * @param    DRequest $request The request.
     *
     * @return    DRemotelyExecutable    The matched remote executable object.
     * @throws    DNotFound    If no matching remotely executable object is found.
     */
    public static function matchRpc($uri, DRequest $request)
    {
        // Convert URI into qualified class name format.
        $qualifiedName = static::uriToQualifiedName($uri);
        // Check if this is a remote procedure.
        $isValid = DClassQuery::load()
                              ->setAncestor('app\\decibel\\rpc\\DRemotelyExecutable')
                              ->isValid($qualifiedName);
        if ($isValid) {
            $rpc = $qualifiedName::loadWithoutParameters();
            if ($request instanceof DEntityBodyRequest) {
                $body = $request->getBody();
            } else {
                $body = null;
            }
            $rpc->applyParameters($request->getParameters(), $body);
        } else {
            throw new DNotFound(
                null,
                new DLabel(
                    self::class,
                    'errorInvalidRpc',
                    array('qualifiedName' => $qualifiedName)
                )
            );
        }

        return $rpc;
    }

    /**
     * Gracefully handles an exception.
     *
     * @param    DException    $exception The exception that occurred.
     * @param    DHttpResponse $error     The error response to return to the client.
     *
     * @throws    DHttpResponse
     * @return    void
     */
    protected function handleException(DException $exception, DHttpResponse $error)
    {
        // Log the exception as an error but continue execution.
        DErrorHandler::logException($exception);
        // Encode the exception into JSON and return to the client.
        $response = DJson::encode($exception);
        $mimeType = DRemoteProcedure::RESULT_TYPE_JSON;
        static::log($mimeType, $response, $this->rpc);
        $stream = new DTextStream($response);
        $error->setBody($stream, $mimeType);
        throw $error;
    }

    /**
     * Determines whether profiling can occur on request executed by this router.
     *
     * @return    bool
     */
    public function profile()
    {
        return $this->rpc
        && $this->rpc->profile();
    }

    /**
     * Converts the provided URI into the format of a qualified class name.
     *
     * @param    string $uri The URI to convert.
     *
     * @return    string
     */
    protected static function uriToQualifiedName($uri)
    {
        $rpcPath = addcslashes(DECIBEL_CORE_RPCPATH, '/');

        return preg_replace(
            array("/^{$rpcPath}/", '/\//', '/\?.*/'),
            array('app/', '\\', ''),
            trim($uri, '/')
        );
    }

    /**
     * Log the current request to the database.
     *
     * @param    string              $mimeType    The mime type of the response content.
     * @param    string              $response    The RPC response content.
     *                                            This is a pointer to conserve memory,
     *                                            however the variable content is not modified.
     * @param    DRemotelyExecutable $rpc         The executed remote procedure.
     *
     * @return    void
     */
    protected static function log($mimeType, &$response, DRemotelyExecutable $rpc)
    {
        if ($rpc->log()) {
            DRpcLog::log(array(
                             'rpc'          => get_class($rpc),
                             'requestData'  => print_r($rpc->getFieldValues(), true),
                             'requestBody'  => htmlentities($rpc->readPostBody()),
                             'mimeType'     => $mimeType,
                             'responseData' => $response,
                         ));
        }
    }

    /**
     * Translates the provided URL and performs the required functionality.
     *
     * @return    void
     * @throws    DBadRequest            If the RPC would not accept the provided paramters.
     * @throws    DNotFound            If the requested remote procedured does not exist.
     * @throws    DPermanentRedirect    If the requested page must be accessed via the HTTPS protocol.
     *
     * @param    DUnauthorised        If authorisation is required to execute this RPC.
     */
    public function run()
    {
        // Ensure errors are not reported to standard output.
        DErrorHandler::allowDump(false);
        // Determine request information.
        $request = $this->request;
        $url = $request->getUrl();
        $uri = $url->getURI();
        // Attempt to match the URI to a remotely executable object.
        $this->rpc = static::matchRpc($uri, $request);
        // Check if HTTPS is required by the rpc. Don't force this in debug
        // mode, as there usually won't be a valid certificate installed.
        static::checkHttps($this->rpc, $request);
        // Attempt to authorise the user.
        $user = $this->getUser();
        static::checkAuthorisation($user, $this->rpc);
        // Set the language.
        DLanguage::initialise();
        // Execute the procedure.
        try {
            $mimeType = $this->rpc->getResultType();
            $output = $this->rpc->execute();
            $stream = new DTextStream($output);
            static::log($mimeType, $output, $this->rpc);
            // Send the response.
            $response = new DOk();
            $response->setBody($stream, $mimeType);
            throw $response;
            // If any other HTTP Response is issued
        } catch (DHttpResponse $httpResponse) {
            // Encode the exception into JSON to be returned to the client
            $response = DJson::encode($httpResponse);
            $mimeType = DRemoteProcedure::RESULT_TYPE_JSON;
            static::log($mimeType, $response, $this->rpc);
            throw $httpResponse;
            // If anything goes wrong, handle the exception gracefully.
        } catch (DException $exception) {
            $this->handleException($exception, new DInternalServerError());
        }
    }
}
