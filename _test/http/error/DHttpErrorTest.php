<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DHttpError;
use app\decibel\stream\DTextStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DHttpErrorTest.
 *
 * @group http
 */
class DHttpErrorTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DHttpError::__construct
     * @covers app\decibel\http\error\DHttpError::getRedirectReason
     * @covers app\decibel\http\error\DHttpError::getRedirectUrl
     * @covers app\decibel\http\error\DHttpError::getResponseHeaders
     */
    public function testCreateWithArguments()
    {
        $redirectUrl = 'http://www.google.co.uk';
        $reason = 'The reason for the error';

        $stub = $this->getMockForAbstractClass(DHttpError::class,
                                               array($redirectUrl, $reason));

        $this->assertSame($reason, $stub->getRedirectReason());
        $this->assertSame($redirectUrl, $stub->getRedirectUrl());
    }

    /**
     * @covers app\decibel\http\error\DHttpError::getBody
     * @covers app\decibel\http\error\DHttpError::generateMetaRedirect
     */
    public function testCreateWithRedirectUrlBody()
    {
        $redirectUrl = 'http://www.google.co.uk';
        /** @var DHttpError $url */
        $url = $this->getMockForAbstractClass(DHttpError::class,
                                              array($redirectUrl));
        /** @var DTextStream $stream */
        $stream = $url->getBody();

        $data = '<meta http-equiv="refresh" content="0; url=' . $redirectUrl . '" />';
        $this->assertStringStartsWith($data, $stream->read());
    }

    /**
     * @covers app\decibel\http\error\DHttpError::getRedirectUrl
     */
    public function testGetRedirectUrlDefaultNull()
    {
        /** @var DHttpError $url */
        $url = $this->getMockForAbstractClass(DHttpError::class);
        $this->assertNull($url->getRedirectUrl());
    }

    /**
     * @covers app\decibel\http\error\DHttpError::getRedirectReason
     */
    public function testGetRedirectReasonDefaultNull()
    {
        /** @var DHttpError $url */
        $url = $this->getMockForAbstractClass(DHttpError::class);
        $this->assertNull($url->getRedirectReason());
    }
}
