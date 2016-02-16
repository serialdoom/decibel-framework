<?php
namespace tests\app\decibel\http;

use app\decibel\http\DETag;
use app\decibel\http\request\DRequest;
use app\decibel\test\DTestCase;

class DETagTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DETag::generate
     */
    public function testGenerate()
    {
        $timestamp = time();
        $mtime     = base_convert(str_pad($timestamp, 16, "0"), 10, 16);
        $this->assertSame('400-' . $mtime,
                          DETag::generate(1024, $timestamp));
    }

    /**
     * @covers app\decibel\http\DETag::match
     */
    public function testMatchWithFalsyEtagReturnsNull()
    {
        $this->assertNull(DETag::match(false));
    }
}
