<?php
namespace tests\app\decibel\http;

use app\decibel\debug\DInvalidMethodException;
use app\decibel\http\debug\DMalformedUrlException;
use app\decibel\http\DUrlParser;
use app\decibel\http\DUrl;
use app\decibel\test\DTestCase;

/**
 * Test class for DUrl.
 */
class DUrlTest extends DTestCase
{
    /**
     * @covers app\decibel\http\DUrl::__construct
     * @covers app\decibel\http\DUrl::define
     */
    public function testCreate()
    {
        $url = new DUrl('uri/');
        $this->assertInstanceOf(DUrl::class, $url);
        $this->assertSame('uri/', $url->getURI());
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toString()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url1 = DUrlParser::parse('http://www.decibeltechnology.com/');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url1);
        $url2 = DUrlParser::parse('http://www.decibeltechnology.com/AboutUs/');
        $this->assertSame('http://www.decibeltechnology.com/AboutUs/', (string)$url2);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function testCreateBadUrlThrowsException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $this->setExpectedException(DInvalidMethodException::class);
        new DUrl('http://www.decibeltechnology.com');
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringEmpty()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url1 = new DUrl('/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url1);
        $this->assertSame('/', (string)$url1);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringUriWithoutSlash()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('AboutUs');
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringUriWithSlashInEnd()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('AboutUs/');
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringUriWithSlashInStart()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/AboutUs');
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringUriWithSlashInStartAndEnd()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/AboutUs/');
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     * @expectedException app\decibel\http\debug\DMalformedUrlException
     */
    public function test__toStringAllParameters_invalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        new DUrl('http://www.decibeltechnology.com:2086/');
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyProtocol()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyHostName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setHostname('decibeltechonology.com');
        $this->assertSame('decibeltechonology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyPort80()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setPort(80);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyPortExcept80()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setPort(2080);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url1 = new DUrl('/');
        $url1->setQueryParameters(array('page' => 2, 'classification' => 3));
        $this->assertSame('/?page=2&classification=3', (string)$url1);
        $url2 = new DUrl('/');
        $url2->setQueryParameters(array());
        $this->assertSame('/', (string)$url2);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringOnlyFragment()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url1 = new DUrl('/');
        $url1->setFragment('bottom');
        $this->assertSame('/#bottom', (string)$url1);
        $url2 = new DUrl('/');
        $url2->setFragment('');
        $this->assertSame('/', (string)$url2);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function test__toStringOnlyFragmentWithException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setFragment('#bottom');
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolAndHost()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('https://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function test__toStringProtocolAndHostWithException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('http://www.decibeltechnology.com');
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(2080);
        $this->assertSame('https://www.decibeltechnology.com:2080/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setURI('AboutUs/');
        $this->assertSame('https://www.decibeltechnology.com/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostPortUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setURI('AboutUs/');
        $url->setPort(80);
        $this->assertSame('http://www.decibeltechnology.com/AboutUs/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setURI('AboutUs/');
        $url->setPort(2080);
        $this->assertSame('http://www.decibeltechnology.com:2080/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostFragment()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setFragment('bottompage');
        $this->assertSame('http://www.decibeltechnology.com/#bottompage', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setFragment('');
        $this->assertSame('https://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostPortFragment()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $url->setFragment('bottompage');
        $this->assertSame('http://www.decibeltechnology.com/#bottompage', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(2080);
        $url->setFragment('');
        $this->assertSame('https://www.decibeltechnology.com:2080/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostFragmentQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array('page' => 2, 'classification' => 3));
        $url->setFragment('bottompage');
        $this->assertSame('http://www.decibeltechnology.com/?page=2&classification=3#bottompage', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array());
        $url->setFragment('bottompage');
        $this->assertSame('http://www.decibeltechnology.com/#bottompage', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array());
        $url->setFragment('');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array('page' => 2, 'classification' => 3));
        $url->setFragment('');
        $this->assertSame('https://www.decibeltechnology.com/?page=2&classification=3', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setQueryParameters(array('page' => 2, 'classification' => 3));
        $this->assertSame('/?page=2&classification=3', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setQueryParameters(array());
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringHostQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array('page' => 2, 'classification' => 3));
        $this->assertSame('www.decibeltechnology.com/?page=2&classification=3', (string)$url);
        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array());
        $this->assertSame('www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::__toString
     */
    public function test__toStringProtocolHostQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array('page' => 2, 'classification' => 3));
        $this->assertSame('http://www.decibeltechnology.com/?page=2&classification=3', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setQueryParameters(array());
        $this->assertSame('https://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringOnlyProtocol()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $this->assertSame('/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $this->assertSame('', DTestCase::executeMethod($url, 'getDomainString'));
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringOnlyHost()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('www.decibeltechnology.com', DTestCase::executeMethod($url, 'getDomainString'));
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringOnlyPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setPort(80);
        $this->assertSame('/', (string)$url);
        $url = new DUrl('/');
        $url->setPort(2080);
        $this->assertSame('/', (string)$url);
        $url = new DUrl('/');
        $url->setPort(80);
        $this->assertSame('', DTestCase::executeMethod($url, 'getDomainString'));
        $url = new DUrl('/');
        $url->setPort(2080);
        $this->assertSame('', DTestCase::executeMethod($url, 'getDomainString'));
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringProtocolPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setPort(80);
        $this->assertSame('/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setPort(2080);
        $this->assertSame('/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setPort(80);
        $this->assertSame('', DTestCase::executeMethod($url, 'getDomainString'));
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setPort(2080);
        $this->assertSame('', DTestCase::executeMethod($url, 'getDomainString'));
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringHostPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $this->assertSame('www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(2080);
        $this->assertSame('www.decibeltechnology.com:2080/', (string)$url);
        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $this->assertSame('www.decibeltechnology.com', DTestCase::executeMethod($url, 'getDomainString'));
        $url = new DUrl('/');
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(2080);
        $this->assertSame('www.decibeltechnology.com:2080', DTestCase::executeMethod($url, 'getDomainString'));
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringProtocolHost()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $this->assertSame('https://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::getDomainString
     */
    public function testgetDomainStringProtocolHostPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTP);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(80);
        $this->assertSame('https://www.decibeltechnology.com/', (string)$url);
        $url = new DUrl('/');
        $url->setProtocol(DUrl::PROTOCOL_HTTPS);
        $url->setHostname('www.decibeltechnology.com');
        $url->setPort(2080);
        $this->assertSame('https://www.decibeltechnology.com:2080/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparse()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url1 = DUrlParser::parse('http://www.decibeltechnology.com/');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url1);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseEmptyString()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseWithOutSlashes()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseNull()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse(null);
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlySlash()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('page=2&classification=3');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/page=2&classification=3/', (string)$url);
        $url = DUrlParser::parse('/page=2&classification=3');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/page=2&classification=3/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyQueryParametersWithQuestionmark()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('?page=2&classification=3');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/?page=2&classification=3', (string)$url);
        $url = DUrlParser::parse('/?page=2&classification=3');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/?page=2&classification=3', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseWithOutEndingSlash()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('http://www.decibeltechnology.com');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseWithUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('http://www.decibeltechnology.com/AboutUs/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('http://www.decibeltechnology.com/AboutUs/', (string)$url);
        $url = DUrlParser::parse('http://www.decibeltechnology.com/AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('http://www.decibeltechnology.com/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('/AboutUs/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/AboutUs/', (string)$url);
        $url = DUrlParser::parse('AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/AboutUs/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseWithOutProtocol()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('www.decibeltechnology.com/AboutUs/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/www.decibeltechnology.com/AboutUs/', (string)$url);
        $url = DUrlParser::parse('www.decibeltechnology.com/AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/www.decibeltechnology.com/AboutUs', (string)$url);
        $url = DUrlParser::parse('/www.decibeltechnology.com/AboutUs');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/www.decibeltechnology.com/AboutUs', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testparseOnlyProtocolWithColonAndSlashes()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('http://');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyProtocol()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('http:');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/', (string)$url);
        $url = DUrlParser::parse('http');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/http/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyDomain()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('www.decibeltechnology.com');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/www.decibeltechnology.com', (string)$url);
        $url = DUrlParser::parse('www.decibeltechnology.com/');
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseOnlyPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse(80);
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/80/', (string)$url);
        $url = DUrlParser::parse(2080);
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('/2080/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testparseOnlyPort80WithColon()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        DUrlParser::parse(':80');
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     * @expectedException app\decibel\debug\DInvalidParameterValueException
     */
    public function testparseOnlyPort2080WithColon()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        DUrlParser::parse(':2080');
    }

    /**
     * @covers app\decibel\http\DUrlParser::parse
     */
    public function testparseWithQueryString()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = DUrlParser::parse('www.decibeltechnology.com/AboutUs/?param1=value1&param2=value2');
        $this->assertSame('/www.decibeltechnology.com/AboutUs/?param1=value1&param2=value2', (string)$url);
        $url = DUrlParser::parse('www.decibeltechnology.com/AboutUs?param1=value1&param2=value2');
        $this->assertSame('/www.decibeltechnology.com/AboutUs?param1=value1&param2=value2', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::getAvailableProtocols
     */
    public function testgetAvailableProtocols()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->assertSame(
            array(
                DUrl::PROTOCOL_HTTP  => 'HTTP',
                DUrl::PROTOCOL_HTTPS => 'HTTPS',
            ),
            DUrl::getAvailableProtocols()
        );
    }

    /**
     * @covers app\decibel\http\DUrl::isRelative
     */
    public function testisRelative()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertTrue($url->isRelative());
        $this->assertSame($url, $url->setProtocol('http'));
        $this->assertTrue($url->isRelative());
        $this->assertSame($url, $url->setHostname('www.decibeltechnology.com'));
        $this->assertFalse($url->isRelative());
    }

    /**
     * @covers app\decibel\http\DUrl::isSecure
     */
    public function testisSecure()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertFalse($url->isSecure());
        $this->assertSame($url, $url->setProtocol('http'));
        $this->assertFalse($url->isSecure());
        $this->assertSame($url, $url->setProtocol('https'));
        $this->assertTrue($url->isSecure());
    }

    /**
     * @covers app\decibel\http\DUrl::setFragment
     * @covers app\decibel\http\DUrl::getFragment
     */
    public function testsetFragment()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertNull($url->getFragment());
        $this->assertSame($url, $url->setFragment('fragment'));
        $this->assertSame('fragment', $url->getFragment());
    }

    /**
     * @covers app\decibel\http\DUrl::setFragment
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetFragment_invalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setFragment('#bottom');
    }

    /**
     * @covers app\decibel\http\DUrl::setHostname
     * @covers app\decibel\http\DUrl::getHostname
     */
    public function testsetHostname()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertNull($url->getHostname());
        $this->assertSame($url, $url->setHostname('www.decibeltechnology.com'));
        $this->assertSame('www.decibeltechnology.com', $url->getHostname());
    }

    /**
     * @covers app\decibel\http\DUrl::setHostname
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetHostnameEmpty()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setHostname('');
    }

    /**
     * @covers app\decibel\http\DUrl::setHostname
     */
    public function testsetHostnameNull()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setHostname(null);
        $this->assertSame('/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::setHostname
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetHostnameWithSlash()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setHostname('www.decibeltechinlogy.com/');
    }

    /**
     * @covers app\decibel\http\DUrl::setHostname
     * @covers app\decibel\http\DUrl::getHostname
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetHostnameWithException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('');
        $url->setHostname('http://www.decibeltechnology.com');
        $this->assertSame('http://www.decibeltechnology.com/', (string)$url);
    }

    /**
     * @covers app\decibel\http\DUrl::setPort
     * @covers app\decibel\http\DUrl::getPort
     */
    public function testsetPort()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertSame(80, $url->getPort());
        $this->assertSame($url, $url->setPort(81));
        $this->assertSame(81, $url->getPort());
    }

    /**
     * @covers app\decibel\http\DUrl::setPort
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetPortString()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $url->setPort('Test');
    }

    /**
     * @covers app\decibel\http\DUrl::setProtocol
     * @covers app\decibel\http\DUrl::getProtocol
     */
    public function testsetProtocol()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertNull($url->getProtocol());
        $this->assertSame($url, $url->setProtocol('https'));
        $this->assertSame('https', $url->getProtocol());
    }

    /**
     * @covers app\decibel\http\DUrl::setProtocol
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetProtocolOnlyNumber()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $url->setProtocol('1234');
    }

    /**
     * @covers app\decibel\http\DUrl::setProtocol
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetProtocolAlphaNumeric()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $url->setProtocol('abc1234');
    }

    /**
     * @covers app\decibel\http\DUrl::setProtocol
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetProtocolSpecialCharacters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $url->setProtocol('abc1-234');
    }

    /**
     * @covers app\decibel\http\DUrl::setQueryParameters
     * @covers app\decibel\http\DUrl::getQueryParameters
     */
    public function testsetQueryParameters()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertSame(array(), $url->getQueryParameters());
        $this->assertSame($url, $url->setQueryParameters(
            array(
                'param1' => 'value1',
                'param2' => 'value2',
            )
        ));
        $this->assertSame(
            array(
                'param1' => 'value1',
                'param2' => 'value2',
            ),
            $url->getQueryParameters()
        );
    }

    /**
     * @covers app\decibel\http\DUrl::getQueryString
     */
    public function testgetQueryStringEmpty()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('url/');
        $this->assertSame('', $url->getQueryString());
    }

    /**
     * @covers app\decibel\http\DUrl::getQueryString
     */
    public function testgetQueryStringKeyValueArray()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('url/');
        $url->setQueryParameters(array('param1' => 'value1', 'param2' => 'value2'));
        $this->assertSame('param1=value1&param2=value2', $url->getQueryString());
        $url->setQueryParameters(array('param1' => 'value1'));
        $this->assertSame('param1=value1', $url->getQueryString());
    }

    /**
     * @covers app\decibel\http\DUrl::getQueryString
     */
    public function testgetQueryStringValueArray()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('url/');
        $url->setQueryParameters(array('value1', 'value2'));
        $this->assertSame('0=value1&1=value2', $url->getQueryString());
        $url->setQueryParameters(array('value1'));
        $this->assertSame('0=value1', $url->getQueryString());
    }

    /**
     * @covers app\decibel\http\DUrl::getQueryString
     */
    public function testgetQueryStringEmptyArray()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('url/');
        $url->setQueryParameters(array());
        $this->assertSame('', $url->getQueryString());
    }

    /**
     * @covers app\decibel\http\DUrl::setURI
     * @covers app\decibel\http\DUrl::getURI
     */
    public function testsetURI()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $this->assertSame('uri/', $url->getURI());
        $this->assertSame($url, $url->setURI('uri2/'));
        $this->assertSame('uri2/', $url->getURI());
        $this->assertSame($url, $url->setURI('uri3'));
        $this->assertSame('uri3/', $url->getURI());
    }

    /**
     * @covers app\decibel\http\DUrl::setURI
     * @expectedException app\decibel\model\debug\DInvalidFieldValueException
     */
    public function testsetURIWithException()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $url = new DUrl('uri/');
        $url->setURI('http://www.google.com/');
    }
}
