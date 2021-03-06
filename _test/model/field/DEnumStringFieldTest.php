<?php
namespace tests\app\decibel\model\field;

use app\decibel\database\schema\DColumnDefinition;
use app\decibel\model\field\DEnumStringField;
use app\decibel\model\field\DField;
use app\decibel\test\DTestCase;

/**
 * Test class for DEnumStringField.
 * Generated by Decibel on 2012-04-12 at 09:08:41.
 */
class DEnumStringFieldTest extends DTestCase
{
    /**
     * @covers app\decibel\model\field\DEnumStringField::castValue
     */
    public function testCastValueNull()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setNullOption('Null');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertNull($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::castValue
     */
    public function testCastValueNullInvalid()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertNull($field->castValue(null));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::castValue
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testCastValueStringInvalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $field->castValue('option3');
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::castValue
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testCastValueNumericInvalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $field->castValue(3);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::castValue
     */
    public function testCastValue()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame('option1', $field->castValue('option1'));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::debugValue
     */
    public function testDebugValueNull()
    {
        $showType = null;
        $field = new DEnumStringField('test', 'Test');
        $field->setNullOption('Null');
        $this->assertSame('NULL [Null]', $field->debugValue(null, $showType));
        $this->assertFalse($showType);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::debugValue
     */
    public function testDebugValue()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $showType = null;
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame('string(7) \'option1\' [value1]', $field->debugValue('option1', $showType));
        $this->assertFalse($showType);
        $this->assertSame('string(7) \'option2\' [value2]', $field->debugValue('option2', $showType));
        $this->assertFalse($showType);
        $this->assertSame('string(7) \'option3\' [-- Unknown Value --]', $field->debugValue('option3', $showType));
        $this->assertFalse($showType);
        //$this->assertSame('Array [-- Unknown Value --]', $field->debugValue(array('option1', $showType)));
        //$this->assertFalse($showType);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getDefinition
     */
    public function testGetDefinition()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DEnumStringField('test', 'Test');
        $definition = $field->getDefinition();
        $this->assertInstanceOf(DColumnDefinition::class, $definition);
        $this->assertTrue($definition->null);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getInternalDataTypeDescription
     */
    public function testGetInternalDataTypeDescription()
    {
        $value = '<code>option[1-3]<\/code>';
        $regex = "/One of {$value}(, {$value})*( or {$value})?/";
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                          ));
        $this->assertRegExp($regex, $field->getInternalDataTypeDescription());
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertRegExp($regex, $field->getInternalDataTypeDescription());
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                              'option3' => 'value3',
                          ));
        $this->assertRegExp($regex, $field->getInternalDataTypeDescription());
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getRandomValue
     */
    public function testGetRandomValue()
    {
        $field = new DEnumStringField('test', 'Test');
        $this->assertNull($field->getRandomValue());
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                              'option3' => 'value3',
                          ));
        $this->assertInternalType('string', $field->getRandomValue());
        $this->assertContains($field->getRandomValue(), array('option1', 'option2', 'option3'));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getRegex
     */
    public function testGetRegex()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame('(option1|option2)', $field->getRegex());
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getStandardDefaultValue
     */
    public function testGetStandardDefaultValue()
    {
        $field = new DEnumStringField('test', 'Test');
        $this->assertNull(null, $field->getDefaultValue());
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::getValues
     */
    public function testGetValues()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ), $field->getValues());
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::isNull
     */
    public function testIsNull()
    {
        $field = new DEnumStringField('test', 'Test');
        $this->assertTrue($field->isNull(null));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::isValidValue
     */
    public function testIsValidValue()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertTrue($field->isValidValue('option1'));
        $this->assertTrue($field->isValidValue('option2'));
        $this->assertFalse($field->isValidValue('option3'));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::setDefaultOptions
     */
    public function testSetDefaultOptions()
    {
        $field = new DEnumStringField('test', 'Test');
        $this->assertSame(50, $field->maxLength);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::setValues
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testSetValuesMaximumKey()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DEnumStringField('test', 'Test');
        $field->setMaxLength(7);
        $field->setValues(array(
                              'option1'  => 'value1',
                              'option10' => 'value2',
                          ));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::setValues
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testSetValuesInvalidKey()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              1         => 'value2',
                          ));
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::setValues
     */
    public function testSetValues()
    {
        $field = new DEnumStringField('test', 'Test');
        $values = array(
            'option1' => 'value1',
            'option2' => 'value2',
        );
        $this->assertSame($field, $field->setValues($values));
        $this->assertSame($values, $field->getValues());
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::toString
     */
    public function testToString()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame('value1', $field->toString('option1'));
        $this->assertSame('value2', $field->toString('option2'));
        $this->assertLabel($field->toString('option3'), 'unknownValue', DField::class);
        $this->assertLabel($field->toString(null), 'unknownValue', DField::class);
    }

    /**
     * @covers app\decibel\model\field\DEnumStringField::toString
     */
    public function testToStringNull()
    {
        $field = new DEnumStringField('test', 'Test');
        $field->setNullOption('Null');
        $field->setValues(array(
                              'option1' => 'value1',
                              'option2' => 'value2',
                          ));
        $this->assertSame('Null', $field->toString(null));
    }
}
