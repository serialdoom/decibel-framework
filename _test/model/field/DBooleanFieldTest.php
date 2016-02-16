<?php
namespace tests\app\decibel\model\field;

use app\decibel\model\field\DBooleanField;
use app\decibel\test\DTestCase;

/**
 * Test class for DBooleanField.
 * Generated by Decibel on 2012-04-12 at 09:08:41.
 */
class DBooleanFieldTest extends DTestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueNull()
    {
        $field = new DBooleanField('test', 'Test');
        $field->setNullOption('Null');
        $this->assertNull($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueNullInvalid()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertFalse($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueString()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertTrue($field->castValue('Yes'));
        $this->assertTrue($field->castValue('yes'));
        $this->assertFalse($field->castValue('No'));
        $this->assertFalse($field->castValue('no'));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueStringInvalid()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertTrue($field->castValue('test'));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueInteger()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertTrue($field->castValue(1));
        $this->assertFalse($field->castValue(0));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::castValue
     */
    public function testCastValueArray()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertTrue($field->castValue(array(1)));
        $this->assertFalse($field->castValue(array()));
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::getStandardDefaultValue
     */
    public function testGetStandardDefaultValue()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertFalse($field->getStandardDefaultValue());
        $field->setNullOption('Null');
        $this->assertNull($field->getStandardDefaultValue());
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::getInternalDataType
     */
    public function testGetInternalDataType()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertSame('boolean', $field->getInternalDataType());
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::getRandomValue
     */
    public function testGetRandomValue()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertInternalType('boolean', $field->getRandomValue());
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertSame(1, $field->size);
        $this->assertTrue($field->unsigned);
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::setSize
     * @expectedException app\decibel\debug\DReadOnlyParameterException
     */
    public function testSetSize()
    {
        $field = new DBooleanField('test', 'Test');
        $field->setSize(1);
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::setUnsigned
     * @expectedException app\decibel\debug\DReadOnlyParameterException
     */
    public function testSetUnsigned()
    {
        $field = new DBooleanField('test', 'Test');
        $field->setUnsigned(true);
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::toString
     */
    public function testToString()
    {
        $field = new DBooleanField('test', 'Test');
        $this->assertLabel($field->toString(true), 'yes', 'app\decibel');
        $this->assertLabel($field->toString(false), 'no', 'app\decibel');
        $this->assertLabel($field->toString(null), 'no', 'app\decibel');
    }

    /**
     * @covers app\decibel\model\field\DBooleanField::toString
     */
    public function testToStringNull()
    {
        $field = new DBooleanField('test', 'Test');
        $field->setNullOption('Null');
        $this->assertLabel($field->toString(true), 'yes', 'app\decibel');
        $this->assertLabel($field->toString(false), 'no', 'app\decibel');
        $this->assertSame('Null', $field->toString(null));
    }
}
