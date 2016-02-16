<?php
namespace tests\app\decibel\registry;

use app\decibel\Decibel;
use app\decibel\registry\DAppRegistry;
use app\decibel\registry\DClassInformation;
use app\decibel\registry\DFileInformation;
use app\decibel\registry\DGlobalRegistry;
use \PHPUnit_Framework_TestCase;

/**
 * Test class for DClassQuery.
 *
 * @author Alex van Andel <avanandel@decibeltechnology.com>
 */
class DFileInformationTest extends PHPUnit_Framework_TestCase
{
    /** @var DGlobalRegistry */
    private $registry;

    /** @var DFileInformation */
    private static $fileInformation = null;

    public function setUp()
    {
        $this->registry = DGlobalRegistry::load();
    }

    /**
     * @covers app\decibel\registry\DFileInformation::getFormatVersion
     */
    public function testGetFormatVersion()
    {
        $information = new DFileInformation($this->registry);
        $this->assertSame(1, $information->getFormatVersion());
    }

    /**
     * @covers app\decibel\registry\DFileInformation::getDependencies
     */
    public function testGetDependencies()
    {
        $information = new DFileInformation($this->registry);
        $this->assertEmpty($information->getDependencies());
    }

    /**
     * @covers app\decibel\registry\DFileInformation::generateDebug
     */
    public function testGenerateDebug()
    {
        $information = new DFileInformation($this->registry);
        $debug = $information->generateDebug();

        $this->assertArrayHasKey('files', $debug);
        $this->assertArrayHasKey('lastUpdated', $debug);
    }

    /**
     * @covers app\decibel\registry\DFileInformation::requiresRebuild
     */
    public function testRequiresRebuild()
    {
        $information = new DFileInformation($this->registry);
        // it does require a rebuild because the DGlobalRegistry
        // is not allowed to rebuild himself
        $this->assertTrue($information->requiresRebuild());
    }

    /**
     * @covers app\decibel\registry\DFileInformation::merge
     */
    public function testMergeBadRegistryReturnsFalse()
    {
        $classInformation = new DClassInformation($this->registry);
        $fileInformation = new DFileInformation($this->registry);

        $this->assertFalse(
            $fileInformation->merge($classInformation)
        );
    }

    /**
     * @covers app\decibel\registry\DFileInformation::getFiles
     * @covers app\decibel\registry\DFileInformation::merge
     */
    public function testMerge()
    {
        // use DAppRegistry to check merge
        $information = $this->getFileInformation();
        $this->assertNotEmpty($information->getFiles());
        // cache count
        $count = count($information->getFiles());
        $this->assertTrue($information->merge($information));
        $this->assertCount($count * 2, $information->getFiles());
    }

    /**
     * @covers app\decibel\registry\DFileInformation::merge
     *
     * Kinda hard to test
     */
    /*public function testMergeBumpsLastUpdated()
    {
        $fileInformation = $this->getFileInformation();
        $fileInformation2 = $this->getFileInformation(false);
        $debug = $fileInformation2->generateDebug();
        // cache lastUpdated of fileInformation2 for later use
        $lastUpdated = $debug['lastUpdated'];
        $debug = $fileInformation->generateDebug();
        $this->assertEquals($lastUpdated, $debug['lastUpdated']);

        $this->assertTrue($fileInformation->merge($fileInformation2));
        $debug = $fileInformation->generateDebug();
        $this->assertNotEquals($lastUpdated, $debug['lastUpdated']);
    }*/

    /**
     * @covers app\decibel\registry\DFileInformation::__sleep
     */
    public function testSleep()
    {
        $information = $this->getFileInformation();
        $sleep = $information->__sleep();

        $this->assertContains('files', $sleep);
        $this->assertContains('lastUpdated', $sleep);
    }

    /**
     * @covers app\decibel\registry\DFileInformation::generateChecksum
     */
    public function testGenerateChecksum()
    {
        // FIXME: this logic seems to be broken
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $information = $this->getFileInformation();
        $debug = $information->generateDebug();
        // the checksum for DFileInformation is generated the following way
        // MD5(serialize(FILES_IN_REGISTRY) . LAST_MD)
        // indexedFiles === files in this case
        $checksum = md5(serialize($information->getFiles()) . $debug['lastUpdated']);

        $this->assertTrue($information->requiresRebuild(uniqid()));
        // $this->assertFalse($information->requiresRebuild($checksum));
    }

    /**
     * @param bool $useCached reuse a previously initialised DFileInformation
     *                        if available this may reduces testing time
     *                        default=True
     *
     * @return DFileInformation
     */
    private function getFileInformation($useCached = true)
    {
        if (!$useCached || self::$fileInformation === null) {
            $app = new Decibel();
            $appRegistry = DAppRegistry::load($app->setRelativePath(''));
            self::$fileInformation = new DFileInformation($appRegistry);
        }
        return self::$fileInformation;
    }
}
