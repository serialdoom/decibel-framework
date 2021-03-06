<?php
namespace tests\decibel\validator;

use app\decibel\test\DTestCase;
use app\decibel\validator\DIpValidator;

/**
 * Test class for DIpValidator.
 * Generated by Decibel on 2011-10-31 at 14:14:03.
 */
class DIpValidatorTest extends DTestCase
{
    /**
     * @covers app\decibel\validator\DIpValidator::validate
     */
    public function testValidateBlank()
    {
        $validator = new DIpValidator();
        $this->assertSame(array(), $validator->validate(''));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     * @covers app\decibel\validator\DIpValidator::validateParts
     * @covers app\decibel\validator\DIpValidator::validatePart
     */
    public function testValidate_valid()
    {
        $validator = new DIpValidator();
        $this->assertSame(array(), $validator->validate('192.168.0.1'));
        $this->assertSame(array(), $validator->validate('1.0.0.1'));
        $this->assertSame(array(), $validator->validate('255.255.255.255'));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     */
    public function testvalidate_notString()
    {
        $validator = new DIpValidator();
        $this->assertSame(1, count($validator->validate(true)));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     * @covers app\decibel\validator\DIpValidator::validateParts
     */
    public function testvalidate_parts()
    {
        $validator = new DIpValidator();
        $this->assertSame(1, count($validator->validate('1.234')));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     * @covers app\decibel\validator\DIpValidator::validateParts
     * @covers app\decibel\validator\DIpValidator::validatePart
     */
    public function testvalidate_nonNumeric()
    {
        $validator = new DIpValidator();
        $this->assertSame(1, count($validator->validate('192.168.0.a')));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     * @covers app\decibel\validator\DIpValidator::validateParts
     * @covers app\decibel\validator\DIpValidator::validatePart
     */
    public function testvalidate_zero()
    {
        $validator = new DIpValidator();
        $this->assertSame(1, count($validator->validate('1.0.0.0')));
        $this->assertSame(1, count($validator->validate('0.0.0.1')));
    }

    /**
     * @covers app\decibel\validator\DIpValidator::validate
     * @covers app\decibel\validator\DIpValidator::validateParts
     * @covers app\decibel\validator\DIpValidator::validatePart
     */
    public function testvalidate_tooLarge()
    {
        $validator = new DIpValidator();
        $this->assertSame(1, count($validator->validate('256.0.0.1')));
    }
}
