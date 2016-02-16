<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\registry;

use app\decibel\application\DApp;

/**
 * Provides a registry of information about an App.
 *
 * @author        Timothy de Paris
 */
class DAppRegistry extends DRegistry
{
    /**
     * The App that specified the scope of the registry.
     *
     * @var        DApp
     */
    protected $app;

    /**
     * Loads a registry with a specified scope.
     *
     * @param    DApp $app        The App that specifies the scope
     *                            of the registry.
     *
     * @return    DAppRegistry
     */
    protected function __construct(DApp $app)
    {
        $this->app = $app;
        $parts = explode('\\', $app->getQualifiedName());
        parent::__construct(
            $app->getRelativePath(),
            DECIBEL_REGISTRY_PATH . end($parts)
        );
    }

    /**
     * Loads the registry for the specified App.
     *
     * @param    DApp $app        The App that specifies the scope
     *                            of the registry.
     *
     * @return    static
     */
    public static function load(DApp $app)
    {
        return new static($app);
    }

    /**
     * Returns the App that defined the scope of this registry.
     *
     * @return    DApp
     */
    public function getApp()
    {
        return $this->app;
    }
}
