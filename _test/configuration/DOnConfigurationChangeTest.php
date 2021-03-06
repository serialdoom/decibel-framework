<?php
namespace tests\app\decibel\configuration;

use app\decibel\configuration\DOnConfigurationChange;
use app\decibel\test\DTestCase;

/**
 * Test class for DOnConfigurationChange.
 * Generated by Decibel on 2012-04-12 at 09:08:49.
 */
class DOnConfigurationChangeTest extends DTestCase
{
    /**
     * @covers app\decibel\configuration\DOnConfigurationChange::getDescription
     */
    public function testgetDescription()
    {
        $this->assertNull(DOnConfigurationChange::getDescription());
    }

    /**
     * @covers app\decibel\configuration\DOnConfigurationChange::getDisplayName
     */
    public function testgetDisplayName()
    {
        $this->assertNull(DOnConfigurationChange::getDisplayName());
    }
}
