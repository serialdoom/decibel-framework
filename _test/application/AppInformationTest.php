<?php
/**
 * User: avanandel
 * Date: 2/8/2016
 * Time: 10:22 AM
 */
namespace tests\app\decibel\application;

use app\decibel\application\DAppInformation;
use app\decibel\Decibel;
use app\decibel\registry\DAppRegistry;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DGlobalRegistry;
use app\decibel\test\DTestCase;

class AppInformationTest extends DTestCase
{
    private $registry;
    public function setUp()
    {
        $this->registry = DGlobalRegistry::load();
    }

    /**
     * @covers app\decibel\application\DAppInformation::generateDebug
     */
    public function testGenerateDebug()
    {
        $information = new DAppInformation($this->registry);
        $debug = $information->generateDebug();

        $this->assertArrayHasKey('apps', $debug);
        $this->assertArrayHasKey('files', $debug);
    }

    /**
     * @covers app\decibel\application\DAppInformation::getApps
     */
    public function testGetApps()
    {
        $appInformation = new DAppInformation($this->registry);
        $this->assertEmpty($appInformation->getApps());

        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $appInformation = new DAppInformation($appRegistry);
        $this->assertNotEmpty($appInformation->getApps());
    }

    /**
     * @covers app\decibel\application\DAppInformation::__sleep
     */
    public function testSleep()
    {
        $information = new DAppInformation($this->registry);
        $sleep = $information->__sleep();

        $this->assertContains('apps', $sleep);
        $this->assertContains('files', $sleep);
    }

    /**
     * @covers app\decibel\application\DAppInformation::getDependencies
     */
    public function testGetDependencies()
    {
        $information = new DAppInformation($this->registry);
        $this->assertSame(
            [
                DFileInformation::class,
                DClassInformation::class
            ],
            $information->getDependencies()
        );
    }

    /**
     * @covers app\decibel\application\DAppInformation::getFormatVersion
     */
    public function testGetFormatVersion()
    {
        $information = new DAppInformation($this->registry);
        $this->assertSame(1, $information->getFormatVersion());
    }

    /**
     * @covers app\decibel\application\DAppInformation::rebuild
     */
    public function testRebuild()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $appInformation = new DAppInformation($appRegistry);
        $debug = $appInformation->generateDebug();
        $this->assertNotEmpty($debug['files']);
        $this->assertContains(Decibel::class, $debug['apps']);
    }

    /**
     * @covers app\decibel\application\DAppInformation::merge
     */
    /*public function testMerge()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $appInformation = new DAppInformation($appRegistry);
        $count = count($appInformation->getApps());
        $this->assertTrue($appInformation->merge($appInformation));
        $this->assertCount($count * 2, $appInformation->getApps());
    }*/

    /**
     * @covers app\decibel\application\DAppInformation::merge
     */
    public function testBadMergeReturnsFalse()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $appInformation = new DAppInformation($appRegistry);
        $fileInformation = $appInformation->getDependency(DFileInformation::class);
        $this->assertFalse($appInformation->merge($fileInformation));
    }

    /**
     * @covers app\decibel\application\DAppInformation::getRegistrationFiles
     */
    public function testGetRegistrationFiles()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $information = new DAppInformation($appRegistry);
        $this->assertContains('Decibel.info.php', $information->getRegistrationFiles());
    }

    /**
     * @covers app\decibel\application\DAppInformation::getTableFiles
     */
    public function testGetTableFiles()
    {
        $app = new Decibel();
        $appRegistry = DAppRegistry::load($app->setRelativePath(''));
        $information = new DAppInformation($appRegistry);
        $this->assertContains('Decibel.tables.xml', $information->getTableFiles());
    }
}
