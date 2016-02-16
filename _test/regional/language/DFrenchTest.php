<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DFrench;
use app\decibel\test\DTestCase;

/**
 * Test class for DFrench.
 */
class DFrenchTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DFrench::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DFrench();
        $this->assertSame(
            'fr',
            $language->getLanguageCode()
        );
    }

    /**
     * @covers app\decibel\regional\language\DFrench::getPluralForm
     */
    public function testgetPluralForm()
    {
        $language = new DFrench();
        $this->assertSame(
            0,
            $language->getPluralForm(0)
        );
        $this->assertSame(
            0,
            $language->getPluralForm(1)
        );
        for ($count = 2; $count <= 10; $count++) {
            $this->assertSame(
                1,
                $language->getPluralForm($count)
            );
        }
    }
}
