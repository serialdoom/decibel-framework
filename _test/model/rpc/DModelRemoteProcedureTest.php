<?php
namespace tests\app\decibel\model\rpc;

use app\decibel\authorise\DGuestUser;
use app\decibel\authorise\DRootUser;
use app\decibel\model\rpc\DModelRemoteProcedure;
use app\decibel\test\DTestCase;

class TestModelRemoteProcedure extends DModelRemoteProcedure
{
    public function execute()
    {
    }
}

/**
 * Test class for DModelRemoteProcedure.
 * Generated by Decibel on 2012-04-12 at 09:08:49.
 */
class DModelRemoteProcedureTest extends DTestCase
{
    /**
     * @covers app\decibel\model\rpc\DModelRemoteProcedure::authorise
     */
    public function testauthorise()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $rpc = TestModelRemoteProcedure::loadWithoutParameters();
        $guest = DGuestUser::create();
        $this->assertFalse($rpc->authorise($guest));
        $root = DRootUser::create();
        $this->assertTrue($rpc->authorise($root));
    }
}
