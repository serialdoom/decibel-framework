<?php
namespace tests\app\decibel\configuration;

use app\decibel\application\DApp;
use app\decibel\configuration\DApplicationMode;
use app\decibel\configuration\DApplicationModeHealthCheck;
use app\decibel\health\DHealthCheckResult;
use PHPUnit_Framework_TestCase;

/**
 * Test class for DApplicationModeHealthCheck.
 */
class DApplicationModeHealthCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function tearDown()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_TEST);
    }

    /**
     * @covers app\decibel\configuration\DApplicationModeHealthCheck::checkHealth
     */
    public function testCheckHealth()
    {
        $healthCheck = new DApplicationModeHealthCheck();
        $results = $healthCheck->checkHealth();
        $this->assertCount(1, $results);
        $this->assertContainsOnlyInstancesOf(DHealthCheckResult::class, $results);
        $this->assertSame('Decibel is currently running in staging mode', $results[0]->getDescription());
    }

    /**
     * @depends tests\app\decibel\configuration\DApplicationModeTest::testIsProductionMode
     * @covers app\decibel\configuration\DApplicationModeHealthCheck::checkHealth
     */
    public function testCheckHealthInProductionMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_PRODUCTION);
        $healthCheck = new DApplicationModeHealthCheck();
        $this->assertEmpty($healthCheck->checkHealth());
    }

    /**
     * @covers app\decibel\configuration\DApplicationModeHealthCheck::getComponentName
     */
    public function testGetComponentName()
    {
        $healthCheck = new DApplicationModeHealthCheck();
        $this->assertEquals('Application Mode', $healthCheck->getComponentName());
    }
}
