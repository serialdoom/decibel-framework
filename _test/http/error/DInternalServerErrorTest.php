<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DInternalServerError;
use app\decibel\test\DTestCase;

/**
 * Test class for DInternalServerError.
 *
 * @group http
 */
class DInternalServerErrorTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DInternalServerError::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DInternalServerError();
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\error\DInternalServerError::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DInternalServerError();
        $this->assertSame('HTTP/1.1 500 Internal Server Error', $response->getResponseType());
    }
}
