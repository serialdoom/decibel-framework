<?php
namespace Decibel\Tests\validator;

use app\decibel\test\DTestCase;
use app\decibel\validator\DColourValidator;

/**
 * Test class for DColourValidator.
 * Generated by Decibel on 2011-10-31 at 14:14:20.
 */
class DColourValidatorTest extends DTestCase
{
    /**
     * @covers app\decibel\validator\DColourValidator::validate
     */
    public function testValidate_invalidCode()
    {
        $model = new DColourValidator();
        $this->assertEquals(1, count($model->validate('5678')));
        $this->assertEquals(1, count($model->validate('#FFF')));
    }

    /**
     * @covers app\decibel\validator\DColourValidator::validate
     */
    public function testValidate_validCode()
    {
        $model = new DColourValidator();
        $this->assertEquals(0, count($model->validate('#FFFFFF')));
        $this->assertSame(array(), $model->validate('#FFFFFF'));
    }
}
