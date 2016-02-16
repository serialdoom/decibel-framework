<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DForbidden;
use app\decibel\security\DDefaultSecurityPolicy;
use app\decibel\security\DForbiddenRequestLog;
use app\decibel\stream\DOutputStream;
use app\decibel\test\DTestCase;

/**
 * Test class for DForbidden.
 */
class DForbiddenTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DForbidden::getStatusCode
     */
    public function testGetStatusCode()
    {
        $response = new DForbidden();
        $this->assertSame(403, $response->getStatusCode());
    }

    /**
     * @covers app\decibel\http\error\DForbidden::getResponseType
     */
    public function testGetResponseType()
    {
        $response = new DForbidden();
        $this->assertSame('HTTP/1.1 403 Forbidden', $response->getResponseType());
    }
}
