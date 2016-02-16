<?php
namespace tests\app\decibel\utility;

use app\decibel\authorise\DUser;
use app\decibel\test\DTestCase;
use app\decibel\utility\DJson;

/**
 * Class DJsonTest
 * @package tests\app\decibel\utility
 */
class DJsonTest extends DTestCase
{
    /**
     * @covers app\decibel\utility\DJson::encode
     */
    public function testEncode()
    {
        $data = [ 'success' => true ];
        $this->assertSame(json_encode($data, JSON_UNESCAPED_UNICODE),
                          DJson::encode($data));
    }
}
