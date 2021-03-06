<?php
namespace tests\app\decibel\model\field;

use app\decibel\model\field\DArrayField;
use app\decibel\test\DTestCase;

/**
 * Test class for DArrayField.
 * Generated by Decibel on 2012-04-12 at 09:08:41.
 */
class DArrayFieldTest extends DTestCase
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
     * @covers app\decibel\model\field\DArrayField::castValue
     */
    public function testCastValueNull()
    {
        $field = new DArrayField('test', 'Test');
        $field->setNullOption('Null');
        $this->assertNull($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DArrayField::castValue
     */
    public function testCastValueNullInvalid()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertNull($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DArrayField::castValue
     */
    public function testCastValueArray()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertSame(array(1, 2), $field->castValue(array(1, 2)));
        $this->assertSame(array(), $field->castValue(array()));
    }

    /**
     * @covers app\decibel\model\field\DArrayField::castValue
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testCastValueInvalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DArrayField('test', 'Test');
        $field->castValue('test');
    }

    /**
     * @covers app\decibel\model\field\DArrayField::getDataType
     */
    public function testGetDataType()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertSame('app\\decibel\\model\\field\\DArrayField', $field->getDataType());
    }

    /**
     * @covers app\decibel\model\field\DArrayField::getInternalDataType
     */
    public function testGetInternalDataType()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertSame('array', $field->getInternalDataType());
    }

    /**
     * @covers app\decibel\model\field\DEnumField::getInternalDataTypeDescription
     */
    //	public function testGetInternalDataTypeDescription() {
    //		$field = new DEnumField('test', 'Test');
    //		$field->setValues(array(
    //			'value1',
    //		));
    //		$this->assertRegExp('One of <code>value1</code>', $field->getInternalDataTypeDescription());
    //		$field->setValues(array(
    //			'value1',
    //			'value2',
    //		));
    //		$this->assertRegExp('One of <code>value1</code> or <code>value2</code>', $field->getInternalDataTypeDescription());
    //		$field->setValues(array(
    //			'value1',
    //			'value2',
    //			'value3',
    //		));
    //		$this->assertRegExp('One of <code>value1</code>, <code>value2</code> or <code>value3</code>', $field->getInternalDataTypeDescription());
    //	}
    /**
     * @covers app\decibel\model\field\DArrayField::getStandardDefaultValue
     */
    public function testGetStandardDefaultValue()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertSame(array(), $field->getDefaultValue());
    }

    /**
     * @covers app\decibel\model\field\DArrayField::isEmpty
     */
    public function testIsEmpty()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertTrue($field->isEmpty(null));
        $this->assertTrue($field->isEmpty(''));
        $this->assertTrue($field->isEmpty(false));
        $this->assertTrue($field->isEmpty(array()));
        $this->assertFalse($field->isEmpty(true));
        $this->assertFalse($field->isEmpty(1));
        $this->assertFalse($field->isEmpty('test'));
        $this->assertFalse($field->isEmpty(array(1)));
    }

    /**
     * @covers app\decibel\model\field\DArrayField::setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $field = new DArrayField('test', 'Test');
        $this->assertFalse($field->isExportable());
    }

    /**
     * @covers app\decibel\model\field\DArrayField::setExportable
     * @expectedException app\decibel\debug\DReadOnlyParameterException
     */
    public function testSetExportable()
    {
        $field = new DArrayField('test', 'Test');
        $field->setExportable(true);
    }

    //	/**
    //	 * @covers app\decibel\model\field\DArrayField::toString
    //	 */
    //	public function testToString() {
    //		$field = new DArrayField('test', 'Test');
    //		$this->assertLabel($field->toString(true), 'yes', 'app\decibel');
    //		$this->assertLabel($field->toString(false), 'no', 'app\decibel');
    //		$this->assertLabel($field->toString(null), 'no', 'app\decibel');
    //	}
    //
    //	/**
    //	 * @covers app\decibel\model\field\DArrayField::toString
    //	 */
    //	public function testToStringNull() {
    //		$field = new DArrayField('test', 'Test');
    //		$field->setNullOption('Null');
    //		$this->assertLabel($field->toString(true), 'yes', 'app\decibel');
    //		$this->assertLabel($field->toString(false), 'no', 'app\decibel');
    //		$this->assertSame('Null', $field->toString(null));
    //	}
}
