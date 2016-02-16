<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DEnglish;
use app\decibel\test\DTestCase;

/**
 * Test class for DEnglish.
 */
class DEnglishTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DEnglish::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DEnglish();
        $this->assertSame(
            'en',
            $language->getLanguageCode()
        );
    }
}
