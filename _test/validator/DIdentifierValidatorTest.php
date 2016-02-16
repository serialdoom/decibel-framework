<?php
namespace Decibel\Tests\validator;

use app\decibel\test\DTestCase;
use app\decibel\validator\DIdentifierValidator;

/**
 * Test class for DIdentifierValidator.
 * Generated by Decibel on 2011-10-31 at 14:14:00.
 */
class DIdentifierValidatorTest extends DTestCase
{
    /**
     * @covers app\decibel\validator\DIdentifierValidator::validate
     */
    public function testvalidate_valid()
    {
        $validator1 = new DIdentifierValidator();
        $this->assertSame(array(), $validator1->validate('a'));
        $this->assertSame(array(), $validator1->validate('abcd'));
        $this->assertSame(array(), $validator1->validate('ab-cd'));
        $this->assertSame(array(), $validator1->validate('ab_cd'));
        $this->assertSame(array(), $validator1->validate('0123abcd'));
    }

    /**
     * @covers app\decibel\validator\DIdentifierValidator::validate
     */
    public function testvalidate_invalidBlank()
    {
        $validator = new DIdentifierValidator();
        $this->assertSame(1, count($validator->validate('')));
    }

    /**
     * @covers app\decibel\validator\DIdentifierValidator::validate
     */
    public function testvalidate_invalidCharacter()
    {
        $validator = new DIdentifierValidator();
        $this->assertSame(1, count($validator->validate('abcd^')));
    }
}
