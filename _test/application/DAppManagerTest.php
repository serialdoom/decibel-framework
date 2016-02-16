<?php
namespace tests\app\decibel\application;

use app\decibel\application\DApp;
use app\decibel\application\DAppInformation;
use app\decibel\application\DAppManager;
use app\decibel\Decibel;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\registry\DOnGlobalHiveUpdate;
use app\decibel\test\DTestCase;

class DAppManagerTest extends DTestCase
{
    /**
     * @covers app\decibel\application\DAppManager::__construct
     */
    public function testCreate()
    {
        $this->assertInstanceOf(DAppManager::class, DAppManager::load());
    }

    /**
     * @covers app\decibel\application\DAppManager::getAppInformation
     */
    /*public function testGetAppInformation()
    {
        $registry = DGlobalRegistry::load();
        $registry->getHive(DAppInformation::class);
    }*/

    /**
     * @covers app\decibel\application\DAppManager::loadRegistrations
     */
    public function testLoadRegistrations()
    {
        $event = new DOnGlobalHiveUpdate();
        $appManager = DAppManager::load();
        $appManager->clearCachedRegistrations($event);
    }

    /**
     * @covers app\decibel\application\DAppManager::getApps
     * @covers app\decibel\application\DAppManager::getAppInformation
     */
    public function testGetApps()
    {
        $appManager = DAppManager::load();
        $apps = $appManager->getApps();
        $this->assertContainsOnlyInstancesOf(DApp::class, $apps);
    }

    /**
     * @covers app\decibel\application\DAppManager::getApp
     */
    public function testGetApp()
    {
        $appManager = DAppManager::load();
        $this->assertInstanceOf(Decibel::class, $appManager->getApp(Decibel::class));
        $this->assertNull($appManager->getApp(''));
    }
}
