<?php
namespace tests\app\decibel\http;

use app\decibel\http\DNotModified;
use app\decibel\test\DTestCase;

/**
 * Test class for DNotModified.
 * @group http
 */
class DNotModifiedTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DNotModified::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DNotModified();
        $this->assertSame(304, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\DNotModified::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DNotModified();
        $this->assertSame('HTTP/1.1 304 Not Modified', $response->getResponseType());
    }
}
