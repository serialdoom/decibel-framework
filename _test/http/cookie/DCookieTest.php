<?php
/**
 * User: avanandel
 * Date: 2/8/2016
 * Time: 11:56 AM
 */
namespace tests\app\decibel\http\cookie;

use app\decibel\http\cookie\DCookie;
use app\decibel\http\request\DDefaultRequestInformation;
use app\decibel\http\request\DRequest;
use app\decibel\test\DTestCase;

class DCookieTest extends DTestCase
{
    /**
     * @covers app\decibel\http\cookie\DCookie::__construct
     */
    public function testGetValue()
    {
        $cookie = new DCookie('cookie', 'value');
        $this->assertSame('value', $cookie->getValue());
    }

    /**
     * @covers app\decibel\http\cookie\DCookie::setHttpOnly
     * @covers app\decibel\http\cookie\DCookie::isHttpOnly
     */
    public function testHttpOnly()
    {
        $cookie = new DCookie('cookie', 'value');
        $httpReadOnlyCookie = $cookie->setHttpOnly();
        $this->assertInstanceOf(DCookie::class, $httpReadOnlyCookie);
        $this->assertSame($cookie, $httpReadOnlyCookie);
        $this->assertTrue($httpReadOnlyCookie->isHttpOnly());
    }

    public function testSecureCookie()
    {
        // $cookie = new DCookie('secure-cookie', 'value');
        // $cookie->setSecure();
    }
}
