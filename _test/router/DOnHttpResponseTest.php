<?php
namespace tests\app\decibel\router;

use app\decibel\http\DRedirect;
use app\decibel\regional\DLabel;
use app\decibel\router\DOnHttpResponse;
use app\decibel\test\DTestCase;

/**
 * Test class for DOnHttpResponseTest.
 */
class DOnHttpResponseTest extends DTestCase
{
    public function setUp()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers    app\decibel\router\DOnHttpResponse::__construct
     * @covers    app\decibel\router\DOnHttpResponse::getResponse
     */
    public function test__construct()
    {
        $response = new DRedirect('http://www.google.co.uk', 'This is the reason for the redirect');
        $event = new DOnHttpResponse($response);
        $this->assertSame(
            $event->getResponse(),
            $response
        );
    }

    /**
     * @covers app\decibel\router\DOnHttpResponse::getDescription
     */
    public function testgetDescription()
    {
        $this->assertEquals(
            new DLabel('app\\decibel\\router\\DOnHttpResponse', 'description'),
            DOnHttpResponse::getDescription()
        );
    }

    /**
     * @covers app\decibel\router\DOnHttpResponse::getDisplayName
     */
    public function testgetDisplayName()
    {
        $this->assertEquals(
            new DLabel('app\\decibel\\router\\DOnHttpResponse', 'name'),
            DOnHttpResponse::getDisplayName()
        );
    }
}
