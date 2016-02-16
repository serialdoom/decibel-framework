<?php
namespace tests\app\decibel\http;

use app\decibel\http\DOk;
use app\decibel\test\DTestCase;

/**
 * Test class for DOk.
 * @group http
 */
class DOkTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DOk::execute
     */
    public function testExecute()
    {
        $response = new DOk();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\http\DOk::execute
     */
    public function testExecutePartialContent()
    {
        $response = new DOk();
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    /**
     * @covers app\decibel\http\DOk::getStatusCode
     */
    public function testGetStatusCode()
    {
        $response = new DOk();
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\DOk::getResponseType
     */
    public function testGetResponseType()
    {
        $response = new DOk();
        $this->assertSame('HTTP/1.1 200 OK', $response->getResponseType());
    }
}
