<?php
namespace tests\app\decibel\http;

use app\decibel\http\error\DForbidden;
use app\decibel\http\request\DGetRequest;
use app\decibel\http\request\DPostRequest;
use app\decibel\http\request\DRequest;
use app\decibel\http\request\DRequestParameters;
use app\decibel\test\DTestCase;
use app\decibel\test\DTestRequestInformation;

/**
 * Test class for DRequest.
 */
class DRequestTest extends DTestCase
{
    /**
     * @covers app\decibel\http\request\DRequest::load
     * @covers app\decibel\http\request\DRequest::__construct
     * @covers app\decibel\http\request\DRequest::__wakeup
     */
    public function testload()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $this->assertInstanceOf('app\\decibel\\http\\DRequest', $request);
    }

    /**
     * @covers app\decibel\http\request\DRequest::__wakeup
     */
    public function test__wakeup()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DPostRequest::METHOD)
                                                     ->setPostParameters(new DRequestParameters(array('test1' => 'value1')));
        $request = DRequest::create($requestInformation);
        $request->__wakeup();
        $this->assertInstanceOf('app\\decibel\\http\\DRequest', $request);
    }

    /**
     * @covers app\decibel\http\request\DRequest::create
     * @covers app\decibel\http\request\DRequest::__construct
     * @covers app\decibel\http\request\DRequest::__wakeup
     */
    public function testcreate()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DPostRequest::METHOD)
                                                     ->setPostParameters(new DRequestParameters(array('test1' => 'value1')));
        $request = DRequest::create($requestInformation);
        $this->assertInstanceOf('app\\decibel\\http\\DRequest', $request);
    }

    /**
     * @covers app\decibel\http\request\DRequest::getMethod
     */
    public function testGetMethod()
    {
        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DPostRequest::METHOD)
                                                     ->setPostParameters(new DRequestParameters(array('test1' => 'value1')));
        $request = DRequest::create($requestInformation);
        $this->assertSame(DPostRequest::METHOD, $request->getMethod());
    }

    /**
     * @covers app\decibel\http\request\DRequest::checkXSite
     */
    public function testcheckXSite_ok()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $this->assertFalse($request->checkXSite('test1/test2/'));
    }

    /**
     * @covers app\decibel\http\request\DRequest::checkXSite
     * @expectedException app\decibel\http\error\DForbidden
     */
    public function testcheckXSite_problem1()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $request->checkXSite('test1/test2/<script>/something');
    }

    /**
     * @covers app\decibel\http\request\DRequest::checkXSite
     * @expectedException app\decibel\http\error\DForbidden
     */
    public function testcheckXSite_problem2()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $request->checkXSite('test1/test2/%3E/something');
    }

    /**
     * @covers app\decibel\http\request\DRequest::getInvalidUriChars
     */
    public function testgetInvalidUriChars()
    {
        $invalidChars = base64_decode('AAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxwdHh9/');
        $this->assertSame($invalidChars, DRequest::getInvalidUriChars());
    }

    /**
     * @covers app\decibel\http\request\DRequest::getUrl
     */
    public function testgetUrl()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD)
                                                     ->setUrlParameters(
                                                         new DRequestParameters(array(
                                                                                    'test1' => 'value1',
                                                                                    'test2' => 'value2',
                                                                                ))
                                                     );
        $request = DRequest::create($requestInformation);
        $url = $request->getUrl();
        $this->assertInstanceOf('app\\decibe\l\http\\DUrl', $url);
        $this->assertSame('http://www.example.com/test/?test1=value1&test2=value2', (string)$url);
    }

    /**
     * @covers app\decibel\http\request\DRequest::getParameters
     */
    public function testGetParametersCount()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD)
                                                     ->setUrlParameters(
                                                         new DRequestParameters(array(
                                                                                    'test1' => 'value1',
                                                                                    'test2' => 'value2',
                                                                                ))
                                                     )
                                                     ->setPostParameters(
                                                         new DRequestParameters(array(
                                                                                    'test3' => 'value3',
                                                                                ))
                                                     );
        $request = DRequest::create($requestInformation);
        $this->assertSame(3, count($request->getParameters()));
    }

    /**
     * @covers app\decibel\http\request\DRequest::getParameters
     */
    public function testGetParametersPostRequestCount()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD)
                                                     ->setUrlParameters(
                                                         new DRequestParameters(array(
                                                                                    'test1' => 'value1',
                                                                                    'test2' => 'value2',
                                                                                ))
                                                     )
                                                     ->setPostParameters(
                                                         new DRequestParameters(array(
                                                                                    'test3' => 'value3',
                                                                                ))
                                                     );
        /** @var DRequest $request */
        $request = DRequest::create($requestInformation);
        $this->assertSame(1, count($request->getParameters()));
    }

    /**
     * @covers app\decibel\http\request\DRequest::getRootDomain
     */
    public function testgetRootDomain()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD);
        $request = DRequest::create($requestInformation);
        $this->assertSame('example.com', $request->getRootDomain());
    }

    /**
     * @covers app\decibel\http\request\DRequest::getRootDomain
     */
    public function testgetRootDomain_levels()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.mywebsite.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD);
        $request = DRequest::create($requestInformation);
        $this->assertSame('example.com', $request->getRootDomain());
    }

    /**
     * @covers app\decibel\http\request\DRequest::getRootDomain
     */
    public function testgetRootDomain_dev()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('example.local')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD);
        $request = DRequest::create($requestInformation);
        $this->assertSame('example.local', $request->getRootDomain());
    }

    /**
     * @covers app\decibel\http\request\DRequest::isHttps
     */
    public function testisHttps_yes()
    {
        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('https')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD)
                                                     ->setUrlParameters(new DRequestParameters(array('param1' => 'value')));
        $request = DRequest::create($requestInformation);
        $this->assertTrue($request->isHttps());
    }

    /**
     * @covers app\decibel\http\request\DRequest::isHttps
     */
    public function testisHttps_no()
    {
        $requestInformation = DTestRequestInformation::create()
                                                     ->setProtocol('http')
                                                     ->setHost('www.example.com')
                                                     ->setUri('test/')
                                                     ->setMethod(DGetRequest::METHOD)
                                                     ->setUrlParameters(new DRequestParameters(array('param1' => 'value')));
        $request = DRequest::create($requestInformation);
        $this->assertFalse($request->isHttps());
    }

    /**
     * @covers app\decibel\http\request\DRequest::checkInvalidRequest
     */
    public function testcheckInvalidRequest_ok()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $this->assertFalse($request->checkInvalidRequest('test1/test2/'));
    }

    /**
     * @covers app\decibel\http\request\DRequest::checkInvalidRequest
     */
    public function testcheckInvalidRequest_problem()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->setExpectedException(DForbidden::class);

        $request = DRequest::load();
        $request->checkInvalidRequest('test1/' . chr(127) . 'test2/');
    }

    /**
     * @covers app\decibel\http\request\DRequest::get
     * @todo   Implement testGet().
     */
    public function testget()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\http\request\DRequest::getAll
     * @todo   Implement testGetAll().
     */
    public function testgetAll()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\http\request\DRequest::getUploadedFile
     * @todo   Implement testGetUploadedFile().
     */
    public function testgetUploadedFile()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\http\request\DRequest::setUri
     */
    public function testsetUri()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $request = DRequest::load();
        $request->setUri('test1/test2');
        $this->assertSame('test1/test2', $request->getUrl()->getURI());
    }
}
