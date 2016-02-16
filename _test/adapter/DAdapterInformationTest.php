<?php
namespace tests\app\decibel\adapter;

use app\decibel\adapter\DAdapterInformation;
use app\decibel\application\DApp;
use app\decibel\database\DDatabaseInformation;
use app\decibel\database\mysql\DMySQL;
use app\decibel\Decibel;
use app\decibel\registry\DAppRegistry;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\test\DTestCase;

/**
 * Test class for DAdapterInformation.
 */
class DAdapterInformationTest extends DTestCase
{
    /** @var DGlobalRegistry */
    private $registry;

    public function setUp()
    {
        $this->registry = DGlobalRegistry::load();
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::generateDebug
     */
    public function testGenerateDebug()
    {
        $information = new DAdapterInformation($this->registry);
        $this->assertArrayHasKey('runtimeAdapters', $information->generateDebug());
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::__sleep
     */
    public function testSleep()
    {
        $information = new DAdapterInformation($this->registry);
        $this->assertSame(
            array(
                'checksum',
                'formatVersion',
                'runtimeAdapters',
            ),
            $information->__sleep()
        );
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::getDependencies
     */
    public function testExpectedDependencies()
    {
        $information = new DAdapterInformation($this->registry);
        $this->assertSame(
            array(
                DFileInformation::class,
                DClassInformation::class,
            ),
            $information->getDependencies()
        );
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::getFormatVersion
     */
    public function testGetFormatVersion()
    {
        $information = new DAdapterInformation($this->registry);
        $this->assertSame(1, $information->getFormatVersion());
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::getAvailableAdapters
     */
    public function testGetAvailableAdapters()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $information = new DAdapterInformation($appRegistry);

        $this->assertNotEmpty($information->getAvailableAdapters(DDatabaseInformation::class));
    }

    /**
     * @covers app\decibel\adapter\DAdapterInformation::getAvailableAdapters
     */
    public function testGetAvailableAdaptersBadRuntimeAdapterReturnsArray()
    {
        $information = new DAdapterInformation($this->registry);
        $this->assertEmpty($information->getAvailableAdapters(DApp::class));
    }

    public function testMergeMultipleAdapterInformations()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
