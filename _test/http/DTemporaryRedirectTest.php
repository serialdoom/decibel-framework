<?php
namespace tests\app\decibel\http;

use app\decibel\http\DTemporaryRedirect;
use app\decibel\test\DTestCase;

/**
 * Test class for DTemporaryRedirectTest.
 * @group http
 */
class DTemporaryRedirectTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DTemporaryRedirect::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DTemporaryRedirect('http://www.redirect.com');
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 307 Temporary Redirect'
        );
    }

    /**
     * @covers app\decibel\http\DTemporaryRedirect::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DTemporaryRedirect('http://www.redirect.com');
        $this->assertSame(307, $response->getStatusCode());
    }
}
