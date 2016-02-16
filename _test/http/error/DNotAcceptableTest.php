<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DNotAcceptable;
use app\decibel\test\DTestCase;

/**
 * Test class for DNotAcceptableTest.
 *
 * @group http
 */
class DNotAcceptableTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DNotAcceptable::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DNotAcceptable();
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 406 Not Acceptable'
        );
    }

    /**
     * @covers app\decibel\http\error\DNotAcceptable::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DNotAcceptable();
        $this->assertSame(406, $response->getStatusCode());
    }
}
