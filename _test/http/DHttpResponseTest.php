<?php
namespace tests\app\decibel\http;

use app\decibel\application\DApp;
use app\decibel\configuration\DApplicationMode;
use app\decibel\http\DHttpResponse;
use app\decibel\http\DOk;
use app\decibel\security\DDefaultSecurityPolicy;
use app\decibel\stream\DOutputStream;
use app\decibel\stream\DTextStream;
use app\decibel\test\DTestCase;

/**
 * Extended class so that we can test the DHttpResponse::execute Method.
 *
 * @group http
 */
class DHttpResponseExtended extends DHttpResponse
{
    protected function getResponseHeaders()
    {
        return array();
    }

    public function getResponseType()
    {
        return 'HTTP/1.1 404 Not Found';
    }

    public function getStatusCode()
    {
        return 404;
    }

    public function testgetBody()
    {
        return $this->getBody();
    }
}

/**
 * Test class for DHttpResponseTest.
 */
class DHttpResponseTest extends DTestCase
{
    /**
     * @throws \app\decibel\debug\DInvalidParameterValueException
     */
    public function tearDown()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_TEST);
    }
    /**
     * @covers app\decibel\http\DHttpResponse::generateDebug
     */
    public function testGenerateDebug()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $this->assertArrayHasKey('headers', $httpResponse->generateDebug());
    }

    /**
     * @covers app\decibel\http\DHttpResponse::getCacheHeaders
     */
    public function testGetCacheHeaders()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('Expires', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertSame('no-cache, must-revalidate, private', $headers['Cache-Control']);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::getMimeHeaders
     */
    public function testGetMimeHeaders()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayNotHasKey('Content-Type', $headers);
        $this->assertArrayNotHasKey('Content-Length', $headers);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::setBody
     * @covers app\decibel\http\DHttpResponse::getMimeHeaders
     */
    public function testGetMimeHeadersWithMimeType()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->setBody(new DTextStream(), 'application/json');
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json; charset=UTF-8', $headers['Content-Type']);
        $this->assertArrayNotHasKey('Content-Length', $headers);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::getMimeHeaders
     */
    public function testGetMimeHeadersWithMimeTypeAndCharset()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->setBody(new DTextStream(), 'application/json', 'ISO-8859-1');
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayNotHasKey('Content-Length', $headers);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::setBody
     * @covers app\decibel\http\DHttpResponse::getMimeHeaders
     * @covers app\decibel\http\DHttpResponse::getContentLength
     */
    public function testGetMimeHeadersWithBody()
    {
        $message = new DTextStream('Hello World');

        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->setBody($message);

        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('Content-Length', $headers);
        $this->assertArrayNotHasKey('Content-Type', $headers);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::setCacheExpiry
     * @covers app\decibel\http\DHttpResponse::getCacheHeaders
     */
    public function testSetCacheExpiry()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->setCacheExpiry(86400*2);

        $headers = $httpResponse->generateDebug()['headers'];

        $this->assertArrayHasKey('Expires', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertSame('public', $headers['Cache-Control']);
    }

    /**
     * Nothing can be cached in Debug mode
     *
     * @covers app\decibel\http\DHttpResponse::setCacheExpiry
     * @covers app\decibel\http\DHttpResponse::getCacheHeaders
     */
    public function testSetCacheExpiryInDebugMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_DEBUG);
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->setCacheExpiry(86400);

        $headers = $httpResponse->generateDebug()['headers'];

        $this->assertArrayHasKey('Expires', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertSame('no-cache, must-revalidate, private', $headers['Cache-Control']);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::addHeader
     */
    public function testAddHeader()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $httpResponse->addHeader('Custom-Header', 'value');
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('Custom-Header', $headers);
        $this->assertSame('value', $headers['Custom-Header']);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::prepareHeaders
     */
    public function testDecibelSourceHeaderIsAddedInDebugMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_DEBUG);
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $headers = $httpResponse->generateDebug()['headers'];
        $this->assertArrayHasKey('X-Decibel-Source', $headers);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::setBody
     * @covers app\decibel\http\DHttpResponse::getBody
     */
    public function testSetBody()
    {
        $stream = new DTextStream('response');
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $this->assertNull($httpResponse->getBody());
        $this->assertSame($httpResponse, $httpResponse->setBody($stream));
        $this->assertSame($stream, $httpResponse->getBody());
    }

    /**
     * @covers app\decibel\http\DHttpResponse::jsonSerialize
     */
    public function testJsonSerialize()
    {
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $jsonSerialized = $httpResponse->jsonSerialize();
        $this->assertSame(
            [
                '_qualifiedName' => DHttpResponse::class,
                'message'        => ''
            ],
            $jsonSerialized
        );
        $this->assertArrayNotHasKey('file', $jsonSerialized);
        $this->assertArrayNotHasKey('line', $jsonSerialized);
    }

    /**
     * @covers app\decibel\http\DHttpResponse::jsonSerialize
     */
    public function testJsonSerializeInDebugMode()
    {
        DApplicationMode::setMode(DApplicationMode::MODE_DEBUG);
        /** @var DHttpResponse $httpResponse */
        $httpResponse = $this->getMockForAbstractClass(DHttpResponse::class);
        $jsonSerialized = $httpResponse->jsonSerialize();
        $this->assertArrayHasKey('_qualifiedName', $jsonSerialized);
        $this->assertSame(DHttpResponse::class, $jsonSerialized['_qualifiedName']);
        $this->assertArrayHasKey('message', $jsonSerialized);
        $this->assertArrayHasKey('file', $jsonSerialized);
        $this->assertArrayHasKey('line', $jsonSerialized);
    }
}
