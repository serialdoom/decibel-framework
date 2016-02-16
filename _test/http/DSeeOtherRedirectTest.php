<?php
namespace tests\app\decibel\http;

use app\decibel\http\DSeeOtherRedirect;
use app\decibel\test\DTestCase;

/**
 * Test class for DSeeOtherRedirectTest.
 * @group http
 */
class DSeeOtherRedirectTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DSeeOtherRedirect::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DSeeOtherRedirect('http://www.redirect.com');
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 303 See Other'
        );
    }

    /**
     * @covers app\decibel\http\DSeeOtherRedirect::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DSeeOtherRedirect('http://www.redirect.com');
        $this->assertSame(303, $response->getStatusCode());
    }
}
