<?php
namespace tests\app\decibel\database\schema;

use app\decibel\database\schema\DColumnDefinition;
use app\decibel\database\schema\DTableDefinition;
use app\decibel\model\field\DField;
use app\decibel\stream\DFileStream;
use app\decibel\test\DTestCase;
use app\decibel\xml\DXPath;
use DOMDocument;

/**
 * Test class for DColumnDefinition.
 * Generated by Decibel on 2011-10-31 at 14:07:42.
 */
class DColumnDefinitionTest extends DTestCase
{
    /**
     * @var DColumnDefinition
     */
    protected $object;

    /** @var string */
    private $xmlFixturePath;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new DColumnDefinition('testing');
        $this->xmlFixturePath = __DIR__ . '/../../_fixtures/xml/';
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::castValueForField
     * return null
     */
    public function testcastValueForField_path1()
    {
        $column = new DColumnDefinition('test');
        $this->assertNull($column->castValueForField(null));
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::castValueForField
     * return integer
     */
    public function testcastValueForField_path2()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $column->setType('int');
        $this->assertSame(10, $column->castValueForField(10));
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::castValueForField
     * return decimal
     */
    public function testcastValueForField_path3()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $column->setType(DField::DATA_TYPE_FLOAT);
        $this->assertSame(0.10, $column->castValueForField(0.10));
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setAutoIncrement
     * @covers app\decibel\database\schema\DColumnDefinition::getAutoIncrement
     * @covers app\decibel\database\schema\DColumnDefinition::getDefaultValue
     */
    public function testsetAutoIncrement()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $column->defaultValue = 10;
        $this->assertSame($column, $column->setAutoIncrement(false));
        $this->assertFalse($column->getAutoincrement());
        $this->assertSame(10, $column->getDefaultValue());
        $this->assertSame($column, $column->setAutoIncrement(true));
        $this->assertTrue($column->getAutoincrement());
        $this->assertNull($column->getDefaultValue());
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setNull
     * @covers app\decibel\database\schema\DColumnDefinition::getNull
     */
    public function testsetNull()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $this->assertSame($column, $column->setNull(true));
        $this->assertTrue($column->getNull());
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setSize
     * @covers app\decibel\database\schema\DColumnDefinition::getSize
     */
    public function testsetSize()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $column = new DColumnDefinition('test');
        $this->assertSame($column, $column->setSize(2));
        $this->assertSame(2, $column->getSize());
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setSize
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetSize_string()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $column = new DColumnDefinition('test');
        $column->setSize('string');
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setType
     */
    public function testsetType()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $column = new DColumnDefinition('test');
        $column->setDefaultValue(10);
        $this->assertSame($column, $column->setType('int'));
        $this->assertSame('int', $column->getType());
        $this->assertSame(10, $column->getDefaultValue());
        $this->assertSame($column, $column->setType('INT'));
        $this->assertSame('int', $column->getType());
        $this->assertSame(10, $column->getDefaultValue());
        $this->assertSame($column, $column->setType('TEXT'));
        $this->assertSame('text', $column->getType());
        $this->assertNull($column->getDefaultValue());
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setUnsigned
     * @covers app\decibel\database\schema\DColumnDefinition::getUnsigned
     */
    public function testsetUnsigned()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $this->assertSame($column, $column->setUnsigned(true));
        $this->assertTrue($column->getUnsigned());
    }

    /**
     * @covers app\decibel\database\schema\DColumnDefinition::setName
     * @covers app\decibel\database\schema\DColumnDefinition::getName
     */
    public function testsetName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $column = new DColumnDefinition('test');
        $this->assertSame($column, $column->setName('text'));
        $this->assertSame('text', $column->getName());
    }
}
