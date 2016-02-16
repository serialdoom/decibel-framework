<?php
namespace tests\app\decibel\ssl;

use app\decibel\ssl\DSslResource;
use app\decibel\test\DTestCase;

class TestSslResource extends DSslResource
{
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function generateDebug()
    {
    }

    public function testGetResource()
    {
        return $this->getResource();
    }
}

/**
 * Test class for DSslResource.
 */
class DSslResourceTest extends DTestCase
{
    /**
     *
     */
    protected function setUp()
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped(
                'The OpenSSL extension is not available.'
            );
        }
    }

    /**
     * @covers app\decibel\ssl\DSslResource::getResource
     */
    public function testGenerateDefault()
    {
        $resource = new TestSslResource('test');
        $this->assertSame('test', $resource->testGetResource());
    }
}
