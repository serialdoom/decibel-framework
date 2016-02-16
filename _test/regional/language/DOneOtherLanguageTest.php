<?php
namespace tests\app\decibel\regional\language;

use app\decibel\test\DTestCase;

/**
 * Test class for DOneOtherLanguage.
 */
class DOneOtherLanguageTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DOneOtherLanguage::getPluralForm
     */
    public function testgetPluralForm()
    {
        $stub = $this->getMockForAbstractClass('app\\decibel\\regional\\language\\DOneOtherLanguage');
        $this->assertSame(
            1,
            $stub->getPluralForm(0)
        );
        $this->assertSame(
            0,
            $stub->getPluralForm(1)
        );
        for ($count = 2; $count <= 10; $count++) {
            $this->assertSame(
                1,
                $stub->getPluralForm($count)
            );
        }
    }
}
