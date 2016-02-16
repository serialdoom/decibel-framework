<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DUnauthorised;
use app\decibel\test\DTestCase;

/**
 * Test class for DUnauthorisedTest.
 *
 * @group http
 */
class DUnauthorisedTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DUnauthorised::execute
     */
    public function testexecute()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\http\error\DUnauthorised::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DUnauthorised();
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 401 Unauthorized'
        );
    }

    /**
     * @covers app\decibel\http\error\DUnauthorised::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DUnauthorised();
        $this->assertSame(401, $response->getStatusCode());
    }
}
