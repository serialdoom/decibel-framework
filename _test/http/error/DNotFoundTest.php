<?php
namespace tests\app\decibel\http\error;

use app\decibel\http\error\DNotFound;
use app\decibel\test\DTestCase;

/**
 * Test class for DNotFoundTest.
 *
 * @group http
 */
class DNotFoundTest extends DTestCase
{
    /**
     * @covers app\decibel\http\error\DNotFound::getResponseType
     */
    public function testgetResponseType()
    {
        $response = new DNotFound();
        $this->assertSame(
            $response->getResponseType(),
            'HTTP/1.1 404 Not Found'
        );
    }

    /**
     * @covers app\decibel\http\error\DNotFound::getStatusCode
     */
    public function testgetStatusCode()
    {
        $response = new DNotFound();
        $this->assertSame(404, $response->getStatusCode());
    }
}
