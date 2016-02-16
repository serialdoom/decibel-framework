<?php
namespace tests\app\decibel\http;

use app\decibel\http\DPermanentRedirect;
use app\decibel\test\DTestCase;

/**
 * Test class for DPermanentRedirectTest.
 *
 * @group http
 */
class DPermanentRedirectTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DPermanentRedirect::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DPermanentRedirect('http://www.redirect.com');
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 301 Moved Permanently'
        );
    }

    /**
     * @covers app\decibel\http\DPermanentRedirect::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DPermanentRedirect('http://www.redirect.com');
        $this->assertSame(301, $response->getStatusCode());
    }
}
