<?php
namespace tests\app\decibel\application;

use app\decibel\application\DAppManager;
use app\decibel\application\DDependencyHealthCheck;
use app\decibel\test\DTestCase;

/**
 * Test class for DDependencyHealthCheck.
 * Generated by Decibel on 2012-04-12 at 09:08:49.
 */
class DDependencyHealthCheckTest extends DTestCase
{
    /**
     * @covers app\decibel\application\DDependencyHealthCheck::getComponentName
     */
    public function testGetComponentName()
    {
        $check = DDependencyHealthCheck::load();
        $this->assertLabel($check->getComponentName(), 'environment', DAppManager::class);
    }
}