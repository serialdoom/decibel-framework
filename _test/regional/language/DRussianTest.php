<?php
namespace tests\app\decibel\regional\language;

use app\decibel\regional\language\DRussian;
use app\decibel\test\DTestCase;

/**
 * Test class for DRussian.
 */
class DRussianTest extends DTestCase
{
    /**
     * @covers app\decibel\regional\language\DRussian::getLanguageCode
     */
    public function testgetLanguageCode()
    {
        $language = new DRussian();
        $this->assertSame(
            'ru',
            $language->getLanguageCode()
        );
    }

    /**
     * @covers app\decibel\regional\language\DRussian::getPluralForm
     */
    public function testgetPluralForm()
    {
        $language = new DRussian();
        /** CATEGORY 1 - One**/
        // eg. $count = 1, 21, 31, 41, 51, 61, 71, 81, 101, 1001, ...
        // $mod10 === 1, $mod100 === 1
        $this->assertSame(
            0,
            $language->getPluralForm(1)
        );
        // $mod10 === 1, $mod100 === 21
        $this->assertSame(
            0,
            $language->getPluralForm(21)
        );
        // $mod10 === 1, $mod100 === 51
        $this->assertSame(
            0,
            $language->getPluralForm(51)
        );
        // $mod10 === 1, $mod100 === 1
        $this->assertSame(
            0,
            $language->getPluralForm(101)
        );
        // $mod10 === 1, $mod100 === 1
        $this->assertSame(
            0,
            $language->getPluralForm(1001)
        );
        /** CATEGORY 2 - Few **/
        // eg. $count = 2~4, 22~24, 32~34, 42~44, 52~54, 62, 102, 1002, ...
        // $mod10 === 2, $mod100 === 2
        $this->assertSame(
            1,
            $language->getPluralForm(2)
        );
        // $mod10 === 2, $mod100 === 22
        $this->assertSame(
            1,
            $language->getPluralForm(22)
        );
        // $mod10 === 4, $mod100 === 34
        $this->assertSame(
            1,
            $language->getPluralForm(34)
        );
        // $mod10 === 3, $mod100 === 43
        $this->assertSame(
            1,
            $language->getPluralForm(43)
        );
        // $mod10 === 2, $mod100 === 62
        $this->assertSame(
            1,
            $language->getPluralForm(62)
        );
        // $mod10 === 2, $mod100 === 2
        $this->assertSame(
            1,
            $language->getPluralForm(1002)
        );
        /** CATEGORY 3 - Many **/
        // $count = 0, 5~19, 100, 1000, 100000, 10000, 1000000, ...
        // $mod10 === 0, $mod100 === 0
        $this->assertSame(
            2,
            $language->getPluralForm(0)
        );
        // $mod10 === 5, $mod100 === 5
        $this->assertSame(
            2,
            $language->getPluralForm(5)
        );
        // $mod10 === 0, $mod100 === 10
        $this->assertSame(
            2,
            $language->getPluralForm(10)
        );
        // $mod10 === 9, $mod100 === 19
        $this->assertSame(
            2,
            $language->getPluralForm(19)
        );
        // $mod10 === 0, $mod100 === 0
        $this->assertSame(
            2,
            $language->getPluralForm(100)
        );
        // $mod10 === 0, $mod100 === 0
        $this->assertSame(
            2,
            $language->getPluralForm(1000000)
        );
        /** CATEGORY 4 - Other **/
        // $count = 0.0~1.5, 10.0, 100.0, 1000.0, 10000.0, ...
    }
}
