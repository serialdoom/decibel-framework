<?php
namespace tests\app\decibel\model\field;

use app\decibel\model\field\DIntegerField;
use app\decibel\test\DTestCase;

/**
 * Test class for DIntegerField.
 * Generated by Decibel on 2012-04-12 at 09:08:41.
 */
class DIntegerFieldTest extends DTestCase
{
    /**
     * @covers app\decibel\model\field\DIntegerField::getInternalDataType
     */
    public function testGetInternalDataType()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame('integer', $field->getInternalDataType());
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::getRegex
     */
    public function testGetRegex()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame('[0-9]+', $field->getRegex());
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::getStandardDefaultValue
     */
    public function testGetStandardDefaultValue()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame(0, $field->getDefaultValue());
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::getStandardDefaultValue
     */
    public function testGetStandardDefaultValueStart()
    {
        $field = new DIntegerField('test', 'Test');
        $field->setStart(10);
        $this->assertSame(10, $field->getDefaultValue());
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame(4, $field->size);
        $this->assertFalse($field->unsigned);
        $this->assertSame(1, $field->step);
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setEnd
     */
    public function testSetEnd()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame($field, $field->setEnd(100));
        $this->assertSame(100, $field->end);
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setEnd
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testSetEndInvalid()
    {
        $field = new DIntegerField('test', 'Test');
        $field->setEnd('100');
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setStart
     */
    public function testSetStart()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame($field, $field->setStart(10));
        $this->assertSame(10, $field->start);
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setStart
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testSetStartInvalid()
    {
        $field = new DIntegerField('test', 'Test');
        $field->setStart('10');
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setStep
     */
    public function testSetStep()
    {
        $field = new DIntegerField('test', 'Test');
        $this->assertSame($field, $field->setStep(2));
        $this->assertSame(2, $field->step);
    }

    /**
     * @covers app\decibel\model\field\DIntegerField::setStep
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testSetStepInvalid()
    {
        $field = new DIntegerField('test', 'Test');
        $field->setStep('2');
    }
}
