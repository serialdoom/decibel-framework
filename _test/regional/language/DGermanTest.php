<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DGerman;
use app\decibel\test\DTestCase;

/**
 * Test class for DGerman.
 */
class DGermanTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DGerman::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DGerman();
        $this->assertSame(
            'de',
            $language->getLanguageCode()
        );
    }
}
