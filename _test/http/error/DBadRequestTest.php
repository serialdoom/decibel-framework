<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DBadRequest;
use app\decibel\test\DTestCase;

/**
 * Test class for DBadRequest.
 *
 * @group http
 */
class DBadRequestTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DBadRequest::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DBadRequest();
        $this->assertSame(400, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\error\DBadRequest::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DBadRequest();
        $this->assertSame('HTTP/1.1 400 Bad Request', $response->getResponseType());
    }
}
