<?php
namespace tests\app\decibel\registry;

use app\decibel\application\DApp;
use app\decibel\configuration\DApplicationMode;
use app\decibel\registry\DAppRegistry;
use app\decibel\registry\DGlobalRegistry;

/**
 * Class DGlobalRegistryTest
 * @package tests\app\decibel\registry
 */
class DGlobalRegistryTest
{
    /**
     * @throws \app\decibel\debug\DInvalidParameterValueException
     */
    public function tearDown()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_TEST);
    }

    public function testIsHiveUpdated() { }
    public function testLoad() { }

    /**
     * @covers app\decibel\registry\DGlobalRegistry::getPrioritisedApps
     * @covers app\decibel\registry\DGlobalRegistry::compareLoadPriority
     */
    public function testGetPrioritisedApps()
    {
        $registry = DGlobalRegistry::load();
        $apps = $registry->getAppRegistries();
        $this->assertContainsOnlyInstancesOf(DApp::class, $apps);
    }

    /**
     * @covers app\decibel\registry\DGlobalRegistry::getHive
     */
    public function testGetHiveInProduction()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_PRODUCTION);
        $registry = DGlobalRegistry::load();
        $registry->getHive(DAppRegistry::class);
    }

    /**
     * @covers app\decibel\registry\DGloba
     */

    /**
     * @covers app\decibel\registry\DGlobalRegistry::getAppRegistries
     */
    public function testGetAppRegistries()
    {
        $registry = DGlobalRegistry::load();
        $appRegistries = $registry->getAppRegistries();
        $this->assertSame($appRegistries, $registry->getAppRegistries());
    }

    /**
     * @covers app\decibel\registry\DGlobalRegistry::loadAppRegistries
     */
    public function testLoadAppRegistries()
    {
        $registry = DGlobalRegistry::load();
        $this->assertContainsOnlyInstancesOf(DAppRegistry::class, $registry->getAppRegistries());
    }

}
