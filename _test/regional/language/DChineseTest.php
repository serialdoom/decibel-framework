<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DChinese;
use app\decibel\test\DTestCase;

/**
 * Test class for DChinese.
 */
class DChineseTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DChinese::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DChinese();
        $this->assertSame(
            'zh',
            $language->getLanguageCode()
        );
    }

    /**
     * @covers app\decibel\regional\language\DChinese::getPluralForm
     */
    public function testgetPluralForm()
    {
        $language = new DChinese();
        for ($count = 0; $count <= 10; $count++) {
            $this->assertSame(
                0,
                $language->getPluralForm($count)
            );
        }
    }
}
