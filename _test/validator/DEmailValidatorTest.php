<?php
namespace tests\decibel\validator;

use app\decibel\test\DTestCase;
use app\decibel\validator\DEmailValidator;

/**
 * Test class for DEmailValidator.
 * Generated by Decibel on 2011-10-31 at 14:13:58.
 */
class DEmailValidatorTest extends DTestCase
{
    /**
     * @covers app\decibel\validator\DEmailValidator::validate
     */
    public function testValidateBlank()
    {
        $validator = new DEmailValidator();
        $this->assertSame(array(), $validator->validate(''));
    }

    /**
     * @covers app\decibel\validator\DEmailValidator::validate
     */
    public function testValidateValid()
    {
        $validator = new DEmailValidator();
        $this->assertSame(array(), $validator->validate('test@test.com'));
    }

    /**
     * @covers app\decibel\validator\DEmailValidator::validate
     */
    public function testValidateInvalid()
    {
        $validator = new DEmailValidator();
        $this->assertSame(1, count($validator->validate('test')));
        $this->assertSame(1, count($validator->validate('@test.com')));
        $this->assertSame(1, count($validator->validate('test.com')));
        $this->assertSame(1, count($validator->validate('test@test..com')));
    }
}
