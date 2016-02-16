<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DSpanish;
use app\decibel\test\DTestCase;

/**
 * Test class for DSpanish.
 */
class DSpanishTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DSpanish::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DSpanish();
        $this->assertSame(
            'es',
            $language->getLanguageCode()
        );
    }
}
