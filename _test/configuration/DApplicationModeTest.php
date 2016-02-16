<?php
namespace tests\app\decibel\configuration;

use app\decibel\application\DApp;
use app\decibel\debug\DInvalidParameterValueException;
use app\decibel\test\DTestCase;
use app\decibel\configuration\DApplicationMode;
use app\decibel\application\DConfigurationManager;

/**
 * Description of DApplicationModeTest
 *
 * @author avanandel <avanandel@decibeltechnologies.com>
 */
class DApplicationModeTest extends DTestCase
{
    /**
     * Reverts mode back to MODE_TEST if changed
     *
     * @throws DInvalidParameterValueException
     */
    public function tearDown()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_TEST);
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::__sleep
     */
    public function testSleep()
    {
        $mode = DApplicationMode::load();
        $this->assertSame(
            array(
                'mode'
            ),
            $mode->__sleep()
        );
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::define
     */
    public function testDefine()
    {
        $applicationMode = DApplicationMode::load();
        $this->assertNull($applicationMode->define());
    }

    /**
     * @dataProvider debugModeProvider
     * @covers       app\decibel\configuration\DApplicationMode::getAvailableModes()
     *
     * @param string $mode
     */
    public function testGetAvailableModes($mode)
    {
        $this->assertArrayHasKey($mode, DApplicationMode::getAvailableModes());
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::setMode
     */
    public function testSetMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_PRODUCTION);
        $this->assertNotSame(DApplicationMode::MODE_TEST, DApplicationMode::getMode());
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::getMode
     */
    public function testGetMode()
    {
        $this->assertContains(
            DApplicationMode::getMode(),
            array_keys(DApplicationMode::getAvailableModes())
        );
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::setMode
     * @covers app\decibel\debug\DInvalidParameterValueException::__construct
     */
    public function testSetModeBadModeThrowsException()
    {
        $this->setExpectedException(DInvalidParameterValueException::class);
        DApplicationMode::setMode(DApplicationMode::class);
    }

    /**
     * @dataProvider debugModeProvider
     * @covers app\decibel\configuration\DApplicationMode::isValidMode
     */
    public function testIsValidMode($mode)
    {
        $this->assertTrue(DApplicationMode::isValidMode($mode));
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::isDebugMode
     */
    public function testIsDebugMode()
    {
        $this->assertFalse(DApplicationMode::isDebugMode());
        DApplicationMode::setMode(DApplicationMode::MODE_DEBUG);
        $this->assertTrue(DApplicationMode::isDebugMode());
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::isTestMode
     */
    public function testIsTestMode()
    {
        $this->assertTrue(DApplicationMode::isTestMode());
    }

    /**
     * @covers app\decibel\configuration\DApplicationMode::isProductionMode
     */
    public function testIsProductionMode()
    {
        $this->assertFalse(DApplicationMode::isProductionMode());
        DApplicationMode::setMode(DApplicationMode::MODE_PRODUCTION);
        $this->assertTrue(DApplicationMode::isProductionMode());
    }

    /**
     * DataProvider for testGetAvailableModes
     *                & testIsValidMode
     *
     * @return array
     */
    public function debugModeProvider()
    {
        return [
            [
                'mode' => DApplicationMode::MODE_TEST,
            ],
            [
                'mode' => DApplicationMode::MODE_DEBUG,
            ],
            [
                'mode' => DApplicationMode::MODE_PRODUCTION,
            ],
        ];
    }
}
