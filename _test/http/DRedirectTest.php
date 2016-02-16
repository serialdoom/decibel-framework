<?php
namespace tests\app\decibel\http;

use app\decibel\application\DApp;
use app\decibel\configuration\DApplicationMode;
use app\decibel\http\DRedirect;
use app\decibel\test\DTestCase;

/**
 * Public wrapper for app\decibel\http\DRedirect for testing purposes
 * @group http
 */
class TestDRedirect extends DRedirect
{
    /**
     * Public wrapper for app\decibel\http\DRedirect::getResponseHeaders for
     * testing.
     */
    public function testgetResponseHeaders()
    {
        return self::getResponseHeaders();
    }
}

/**
 * Test class for DRedirectTest.
 */
class DRedirectTest extends DTestCase
{
    /**
     * @throws \app\decibel\debug\DInvalidParameterValueException
     */
    public function tearDown()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_TEST);
    }
    /**
     * @covers app\decibel\http\DRedirect::__construct
     * @covers app\decibel\http\DRedirect::getRedirectUrl
     * @covers app\decibel\http\DRedirect::getRedirectReason
     * @covers app\decibel\http\DRedirect::getResponseType
     */
    public function test__construct()
    {
        $response = new DRedirect('http://www.google.co.uk', 'This is the redirect reason');
        $this->assertSame(
            $response->getRedirectUrl(),
            'http://www.google.co.uk'
        );
        $this->assertSame(
            $response->getRedirectReason(),
            'This is the redirect reason'
        );
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 302 Found'
        );
    }

    /**
     * @covers app\decibel\http\DRedirect::getResponseHeaders
     */
    public function testgetResponseHeaders()
    {
        $response = new TestDRedirect('http://www.google.co.uk', 'This is the redirect reason');
        $this->assertSame(
            $response->testgetResponseHeaders(),
            array(
                'Location' => 'http://www.google.co.uk',
            )
        );
    }

    /**
     * @covers app\decibel\http\DRedirect::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new TestDRedirect('http://www.redirect.com');
        $this->assertSame(302, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\DRedirect::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $redirectUrl = 'http://www.redirect.com';
        $response = new DRedirect($redirectUrl);
        $data = $response->jsonSerialize();
        $this->assertSame(json_encode($data),
                          json_encode($response));
        $this->assertArrayHasKey('url', $data);
        $this->assertSame($redirectUrl, $data['url']);
    }

    /**
     * @covers app\decibel\http\DRedirect::jsonSerialize
     */
    public function testJsonSerializeInDebugMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_DEBUG);
        $redirectUrl = 'http://www.redirect.com';
        $response = new DRedirect($redirectUrl);
        $data = $response->jsonSerialize();
        $this->assertArrayHasKey('reason', $data);
    }
}
