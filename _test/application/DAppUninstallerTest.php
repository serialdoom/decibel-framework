<?php
namespace tests\app\decibel\application;

use app\decibel\application\DApp;
use app\decibel\application\DAppUninstaller;
use app\decibel\application\debug\DMissingAppManifestException;
use app\decibel\Decibel;
use app\decibel\file\DFileNotFoundException;
use app\decibel\test\DTestCase;
use app\decibel\utility\DResult;

/**
 * Test class for DAppUninstaller.
 */
class DAppUninstallerTest extends DTestCase
{
    /**
     * @covers app\decibel\application\DAppUninstaller::canUninstall
     */
    public function testCanUninstall()
    {
        $app = new Decibel();

        $uninstaller = DAppUninstaller::adapt($app->setRelativePath(''));
        $result = $uninstaller->canUninstall();

        $this->assertInstanceOf(DResult::class, $result);
        $this->assertFalse($result->isSuccessful());
    }

    /**
     * @covers app\decibel\application\DAppUninstaller::getAdaptableClass
     */
    public function testGetAdaptableClass()
    {
        $this->assertSame(DApp::class, DAppUninstaller::getAdaptableClass());
    }

    /**
     * @covers app\decibel\application\DAppUninstaller::uninstall
     */
    public function testUninstall()
    {
        $this->setExpectedException(DMissingAppManifestException::class);

        $app = $this->getMockForAbstractClass(DApp::class);

        $uninstaller = DAppUninstaller::adapt($app->setRelativePath(''));
        $uninstaller->uninstall();
    }
}
