<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\router;

use app\decibel\cache\DCache;
use app\decibel\cache\DCacheHandler;
use app\decibel\debug\DErrorHandler;
use app\decibel\debug\DProfiler;
use app\decibel\event\DDispatchable;
use app\decibel\event\DEventDispatcher;
use app\decibel\http\debug\DHttpException;
use app\decibel\http\debug\DMalformedUrlException;
use app\decibel\http\DHttpResponse;
use app\decibel\http\error\DForbidden;
use app\decibel\http\error\DHttpError;
use app\decibel\http\request\DRequest;
use app\decibel\registry\DClassQuery;
use app\decibel\security\DIpAddress;
use app\decibel\utility\DSession;

/**
 * Base class for routers providing functonality to translate a URL into
 * an action within the framework.
 *
 * @author        Timothy de Paris
 */
abstract class DRouter implements DDispatchable
{
    use DEventDispatcher;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::router::DOnHttpError DOnHttpError}
     * event.
     *
     * @var        string
     */
    const ON_HTTP_ERROR = DOnHttpError::class;

    /**
     * Reference to the qualified name of the
     * {@link app::decibel::router::DOnHttpResponse DOnHttpResponse}
     * event.
     *
     * @var        string
     */
    const ON_HTTP_RESPONSE = DOnHttpResponse::class;

    /**
     * 'Pattern' router detail key.
     *
     * @var        string
     */
    const ROUTER_DETAIL_PATTERN = 'pattern';

    /**
     * 'Priority' router detail key.
     *
     * @var        string
     */
    const ROUTER_DETAIL_PRIORITY = 'priority';

    /**
     * 'Router' router detail key.
     *
     * @var        string
     */
    const ROUTER_DETAIL_ROUTER = 'router';

    /**
     * The router handling the request.
     *
     * @var        DRouter
     */
    public static $router;

    /**
     * Information about the request that will be handled by this router.
     *
     * @var        DRequest
     */
    protected $request;

    /**
     * Prepares the application to run.
     *
     * The constructor will define required constants, start the application
     * and register any appropriate shutdown functions.
     *
     * @param    DRequest $request Information about the request.
     *
     * @return    DRouter
     */
    final protected function __construct(DRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Checks that the client IP address is authorised to make requests
     * to this server.
     *
     * @param    DRequest $request The request.
     *
     * @return    void
     * @throws    DForbidden    If the client IP address is banned by the configured
     *                        security policy.
     */
    protected static function checkIpAddress(DRequest $request)
    {
        $session = DSession::load();
        $sessionStarted = $session->isStarted();
        // Check that the current IP address is not blocked.
        if (!$sessionStarted
            || !isset($session['app\\decibel\\router\\DRouter-ipChecked'])
        ) {
            $ipAddress = $request->getIpAddress();
            if (DECIBEL_CORE_BLOCKIPS
                && DIpAddress::checkIpAddress($ipAddress, DIpAddress::FLAG_BLOCKED)
            ) {
                throw new DForbidden(
                    null,
                    'Client IP address is banned by the configured security policy.'
                );
            }
            if ($session->isStarted()) {
                $session['app\\decibel\\router\\DRouter-ipChecked'] = true;
            }
        }
    }

    /**
     * Compares the priority of two routers for array sorting purposes.
     *
     * @param    DRouter $r1 The first router.
     * @param    DRouter $r2 The second router.
     *
     * @return int
     */
    private static function comparePriority($r1, $r2)
    {
        $r1 = $r1[ self::ROUTER_DETAIL_PRIORITY ];
        $r2 = $r2[ self::ROUTER_DETAIL_PRIORITY ];
        if ($r1 < $r2) {
            $compare = -1;
        } else {
            if ($r1 > $r2) {
                $compare = 1;
            } else {
                $compare = 0;
            }
        }

        return $compare;
    }

    /**
     *
     */
    public function execute()
    {
        try {
            $this->run();
            // Execute the HTTP response, if one was received.
        } catch (DHttpResponse $response) {
            if ($response instanceof DHttpError) {
                $event = new DOnHttpError($response);
                $this->notifyObservers($event);
            }
            $event = new DOnHttpResponse($response);
            $this->notifyObservers($event);
            DProfiler::startProfiling('Decibel::flush');
            $response->execute();
        }
    }

    /**
     * Returns a list of available routers, and their paths and priorities.
     *
     * @return    array
     */
    protected static function getAvailableRouters()
    {
        $cache = DCache::load();
        $routers = $cache->get('app\\decibel\\router\\DRouter_routers');
        if ($routers === null) {
            $routerClasses = DClassQuery::load()
                                        ->setAncestor(self::class)
                                        ->getClassNames();
            $routers = array();
            foreach ($routerClasses as $router) {
                $routers[] = array(
                    self::ROUTER_DETAIL_ROUTER   => $router,
                    self::ROUTER_DETAIL_PATTERN  => $router::getPattern(),
                    self::ROUTER_DETAIL_PRIORITY => $router::getPriority(),
                );
            }
            // Sort the routers in priority order
            uasort($routers, array(self::class, 'comparePriority'));
            $cache->set('app\\decibel\\router\\DRouter_routers', $routers);
        }

        return $routers;
    }

    /**
     * Returns the name of the default event for this dispatcher.
     *
     * @return    string    The default event name.
     */
    public static function getDefaultEvent()
    {
        return self::ON_HTTP_RESPONSE;
    }

    /**
     * Returns names of the events produced by this dispatcher.
     *
     * @return    array    An array containing the names of events produced
     *                    by this dispatcher.
     */
    public static function getEvents()
    {
        return array(
            self::ON_HTTP_RESPONSE,
            self::ON_HTTP_ERROR,
        );
    }

    /**
     * Returns the language in which content will be displayed by this router.
     *
     * @return    string    The language code.
     */
    public function getLanguageCode()
    {
        return DECIBEL_REGIONAL_DEFAULTLANGUAGE;
    }

    /**
     * Returns a number between 0 and 10 showing the priority of this router.
     * Lower numbers have higher priority and will be tested first.
     *
     * The default load priority is 5. This method should be overridden if a
     * higher or lower load order is required.
     *
     * @return    int
     */
    public static function getPriority()
    {
        return 5;
    }

    /**
     * Determines which router will be used to fulfill the request and returns
     * an instance of the router.
     *
     * The selected router will be stored against the {@link DRouter::$router}
     * static variable if this method is called without a $uri parameter.
     *
     * @param    DRequest $request        The request to return a router for.
     *                                    If not specified, the current request will be used.
     *
     * @return    DRouter
     * @throws    DForbidden        If the client IP address is banned
     *                            by the configured security policy,
     *                            or the requested URL is malformed.
     */
    final public static function getRouter(DRequest $request = null)
    {
        if ($request === null) {
            try {
                $request = DRequest::load();
            } catch (DMalformedUrlException $exception) {
                throw new DForbidden('/', 'Malformed URL requested.');
            }
        }
        self::checkIpAddress($request);
        // Start the profiler.
        if (defined(DProfiler::PROFILER_ENABLED)) {
            DProfiler::stopProfiling('Decibel::startup');
            DProfiler::startProfiling('Decibel::prepare');
        }
        // Ensure correct shutdown if the process ends unexpectedly.
        register_shutdown_function(array(DErrorHandler::class, 'send500'));
        register_shutdown_function(array(DErrorHandler::class, 'dumpErrors'));
        register_shutdown_function(array(DCacheHandler::class, 'flush'));
        // Find the appropriate router for the request.
        $routers = self::getAvailableRouters();
        $router = self::matchRouter($routers, $request);
        self::$router = $router;

        return $router;
    }

    /**
     * Returns the authenticated user for this request.
     *
     * @return    DUser
     */
    abstract public function getUser();

    /**
     * Matches a URI to a router.
     *
     * @param    array    $routers List of available routers.
     * @param    DRequest $request
     *
     * @return    DRouter    The matched router, or <code>null</code> if no router
     *                    can be matched to the provided URI.
     */
    protected static function matchRouter(array &$routers, DRequest $request)
    {
        $uri = $request->getUrl()
                       ->getURI();
        $router = null;
        foreach ($routers as $routerData) {
            $routerClass = $routerData[ self::ROUTER_DETAIL_ROUTER ];
            $pattern = $routerData[ self::ROUTER_DETAIL_PATTERN ];
            if (preg_match($pattern, $uri)) {
                $router = new $routerClass($request);
                break;
            }
        }

        return $router;
    }

    /**
     * Determines whether profiling can occur on request executed by this router.
     *
     * @note
     * By default, profiling is enabled on all routers.
     *
     * @return    bool
     */
    public function profile()
    {
        return true;
    }

    /**
     * Translates the URL and performs required functionality.
     *
     * @return    void
     * @throws    DHttpException    If the router is unable to satify the request.
     */
    abstract protected function run();

    /**
     * Determines if errors should be reported to standard out (if enabled
     * in the application configuration.
     *
     * @return    bool
     */
    protected function reportErrors()
    {
        return true;
    }
}
