<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\rpc;

use app\decibel\cache\DPublicCache;
use app\decibel\debug\DException;
use app\decibel\http\request\DRequest;
use app\decibel\http\request\DRequestParameters;
use app\decibel\router\DRpcRouter;
use app\decibel\rpc\DRemoteProcedure;
use app\decibel\rpc\DRemoteProcedureCall;
use app\decibel\rpc\DRemoteProcedureInformation;
use app\decibel\rpc\debug\DInvalidRemoteProcedureCallException;
use app\decibel\rpc\debug\DInvalidRemoteProcedureException;
use app\decibel\test\DTestRequestInformation;
use app\decibel\utility\DJson;
use ArrayIterator;
use Exception;

/**
 * Wrapper class for calling a executing a procedure on a remote server.
 *
 * A fluent interface (see http://en.wikipedia.org/wiki/Fluent_interface)
 * is provided to allow chained function calls to build the request,
 * for example:
 *
 * @code
 * use app\decibel\rpc\DRemoteProcedureCall;
 *
 * $response = DRemoteProcedureCall::create('app\\decibel\\configuration\\DSelectApplicationMode')
 *        ->setServer('application.com')
 *        ->setCredentials('username', 'password')
 *        ->setParameter('mode', 'debug')
 *        ->execute($mimeType);
 * @endcode
 *
 * @author    Timothy de Paris
 */
class DRemoteProcedureCall extends DCurlRequest
{
    /**
     * Protocol through which to make the call.
     *
     * @var        string
     */
    protected $protocol;

    /**
     * Qualified name of the remote procedure to call.
     *
     * @var        string
     */
    protected $remoteProcedure;

    /**
     * The host name of the remote server, or <code>null</code>
     * to use this server.
     *
     * @var        string
     */
    protected $hostname = null;

    /**
     * Creates a new remote procedure call.
     *
     * @param    string $remoteProcedure      Qualified name of the remote
     *                                        procedure to call.
     *
     * @return    DRemoteProcedureCall
     */
    protected function __construct($remoteProcedure)
    {
        $this->remoteProcedure = $remoteProcedure;
    }

    /**
     * Creates a new remote procedure call.
     *
     * @param    string $remoteProcedure      Qualified name of the remote
     *                                        procedure to call.
     *
     * @return    static
     */
    public static function create($remoteProcedure)
    {
        return new static($remoteProcedure);
    }

    /**
     * Executes the procedure on the remote server.
     *
     * @param    string $mimeType     Pointer in which the mime type returned
     *                                by the remote server will be stored.
     *
     * @return    mixed    The result of the remote procedure.
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
        // Execute locally if no remote hostname specified.
        $request = DRequest::load();
        if ($this->hostname === null
            || $this->hostname === $request->getUrl()->getHostname()
        ) {
            $result = $this->executeLocally($mimeType);
        } else {
            // Determine the remote URL to be called.
            $information = $this->getRemoteProcedureInformation();
            $this->url = $information->url;
            $result = parent::execute($mimeType);
        }

        return $result;
    }

    /**
     * Executes the procedure on the local server.
     *
     * @param    string $mimeType     Pointer in which the mime type returned
     *                                by the remote server will be stored.
     *
     * @return    mixed    The result of the remote procedure.
     * @throws    DInvalidRemoteProcedureCallException    If the remote server is
     *                                                    unable to execute the
     *                                                    remote procedure.
     */
    public function executeLocally(&$mimeType = null)
    {
        // Build a request object for local execution.
        $parameters = new ArrayIterator($this->parameters);
        $originalRequest = DRequest::load();
        $originalUrl = $originalRequest->getUrl();
        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol($originalUrl->getProtocol())
                                                     ->setHost($originalUrl->getHostname())
                                                     ->setUri($this->remoteProcedure)
                                                     ->setMethod(DPost::METHOD)
                                                     ->setPostParameters(new DRequestParameters((array)$parameters));
        $request = DRequest::create($requestInformation);
        // Validate the remote procedure exists.
        $remoteProcedure = DRpcRouter::matchRpc($this->remoteProcedure, $request);
        if ($remoteProcedure === null) {
            throw new DInvalidRemoteProcedureException($this->remoteProcedure);
        }
        $result = $remoteProcedure->execute();
        // Decode result if json mime type returned.
        $mimeType = $remoteProcedure->getResultType();
        if ($mimeType === DRemoteProcedure::RESULT_TYPE_JSON) {
            $result = DJson::decode($result);
        }

        return $result;
    }

    /**
     * Retrieves information about the remote procedure to be executed
     * from the target server.
     *
     * @return    DRemoteProcedureInformation
     * @throws    DInvalidRemoteProcedureCallException    If the remote server is
     *                                                    unable to execute the
     *                                                    remote procedure.
     */
    public function getRemoteProcedureInformation()
    {
        if ($this->hostname === null) {
            $qualifiedName = $this->remoteProcedure;
            $remoteProcedure = $qualifiedName::load();

            return $remoteProcedure->getInformation();
        }
        // Check if there is a handshake in the cache.
        $publicCache = DPublicCache::load();
        $cacheKey = "{$this->hostname}_{$this->remoteProcedure}";
        $result = $publicCache->retrieve(DRemoteProcedureCall::class, $cacheKey);
        if ($result === null) {
            // The handshake will be at '/remote/decibel/rpc/DHandshake'
            // regardless of the foreign server configuration.
            $url = "{$this->protocol}://{$this->hostname}/remote/decibel/rpc/DHandshake";
            try {
                $result = DCurlRequest::create($url)
                                      ->setParameter('remoteProcedure', $this->remoteProcedure)
                                      ->execute();
                // Store in the cache to avoid multiple simultaneous
                // handshakes between the two servers.
                $publicCache->set(
                    DRemoteProcedureCall::class,
                    $cacheKey,
                    $result,
                    time() + 86400
                );
            } catch (DInvalidRemoteProcedureCallException $e) {
                throw new DInvalidRemoteProcedureCallException(
                    $this->hostname,
                    $e->getMessage()
                );
            }
        }
        if ($result instanceof Exception) {
            throw $result;
        }

        return $result;
    }

    /**
     * Specifies the remote server on which this remote procedure will
     * be executed.
     *
     * @note
     * If this method is not called, the remote procedure will be executed
     * on this server.
     *
     * @param    string $hostname     The host name of the remote server,
     *                                for example <code>www.mywebsite.com</code>
     * @param    string $protocol     The protocol through which to make the request.
     * @param    int    $port         The port on the remote server on which the
     *                                application is running.
     *
     * @return    static
     */
    public function setServer($hostname, $protocol = 'http', $port = null)
    {
        // Normalise hostname (in case protocol or trailing slash are passed).
        $hostname = preg_replace('/(^https?:\/\/|\/?)/', '', $hostname);
        $this->protocol = $protocol;
        $this->hostname = $hostname;
        if ($port) {
            $this->hostname .= ":{$port}";
        }

        return $this;
    }
}
