<?php
namespace tests\app\decibel\configuration;

use app\decibel\authorise\DPrivilege;
use app\decibel\configuration\DConfiguration;
use app\decibel\configuration\DConfigurationStoreInterface;
use app\decibel\configuration\DDefaultConfigurationStore;
use app\decibel\configuration\DInMemoryConfigurationStore;
use app\decibel\configuration\DOnConfigurationChange;
use app\decibel\model\field\DTextField;
use app\decibel\test\DTestCase;

class TestConfiguration extends DConfiguration
{
    public function define()
    {
        $title = new DTextField('title', 'Title');
        $this->addField($title);

        $default = new DTextField('default', 'Default');
        $default->setDefault('Default');
        $this->addField($default);
    }

    /**
     * Returns the {@link DConfigurationStoreInterface} in which this configuration is stored.
     *
     * @return DConfigurationStoreInterface
     */
    /*public static function getConfigurationStore()
    {
        return DInMemoryConfigurationStore::load();
    }*/
}

class DConfigurationTest extends DTestCase
{
    /**
     * @covers app\decibel\configuration\DConfiguration::load
     */
    public function testLoad()
    {
        $this->assertInstanceOf(TestConfiguration::class, TestConfiguration::load());
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::getConfigurationStore
     */
    public function testGetConfigurationStore()
    {
        $this->assertInstanceOf(DDefaultConfigurationStore::class,
                                DConfiguration::getConfigurationStore());
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::__sleep
     */
    public function testSleep()
    {
        /** @var DConfiguration $configuration */
        $configuration = $this->getMockForAbstractClass(DConfiguration::class);
        $this->assertSame(
            [
                'fieldValues'
            ],
            $configuration->__sleep()
        );
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::__wakeup
     * @covers app\decibel\configuration\DConfiguration::loadDefinitions
     */
    public function testWakeup()
    {
        /** @var DConfiguration $configuration */
        $configuration = TestConfiguration::load();
        $serialized = serialize($configuration);
        $compare = unserialize($serialized);
        $this->assertCount(2, $compare->fields);
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::getDefaultEvent
     */
    public function testGetDefaultEvent()
    {
        $this->assertSame(DOnConfigurationChange::class, DConfiguration::getDefaultEvent());
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::__construct
     * @covers app\decibel\configuration\DConfiguration::__wakeup
     */
    public function testCreate()
    {
        /** @var DConfiguration $configuration */
        $configuration = $this->getMockForAbstractClass(DConfiguration::class);
        $this->assertInstanceOf(DConfiguration::class, $configuration);
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::getEvents
     */
    public function testGetEvents()
    {
        $this->assertSame(
            array(
                DOnConfigurationChange::class
            ),
            DConfiguration::getEvents()
        );
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::getRequiredPrivilege
     */
    public function testGetRequiredPrivilege()
    {
        $this->assertSame(DPrivilege::ROOT,
                          DConfiguration::getRequiredPrivilege());
    }

    /**
     * @covers app\decibel\configuration\DConfiguration::save
     */
    public function testSave()
    {
        $configuration = TestConfiguration::load();
        $this->assertTrue($configuration->save());
    }
}
