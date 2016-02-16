<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DDutch;
use app\decibel\test\DTestCase;

/**
 * Test class for DDutch.
 */
class DDutchTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DDutch::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DDutch();
        $this->assertSame(
            'nl',
            $language->getLanguageCode()
        );
    }
}
