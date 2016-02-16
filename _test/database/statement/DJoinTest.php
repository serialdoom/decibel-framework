<?php
namespace tests\app\decibel\database\statement;

use app\decibel\database\statement\DJoin;
use app\decibel\test\DTestCase;

/**
 * Test class for DJoin.
 * Generated by Decibel on 2011-10-31 at 14:08:07.
 */
class DJoinTest extends DTestCase
{
    /**
     * @covers app\decibel\database\statement\DJoin::__toString
     */
    public function test__toString()
    {
        $join = new DJoin('TableName', 'Test', 'Alias', 'Where');
        $this->assertInternalType('object', $join);
        $this->assertObjectHasAttribute('alias', $join);
        $this->assertObjectHasAttribute('table', $join);
        $this->assertObjectHasAttribute('on', $join);
        $this->assertObjectHasAttribute('where', $join);
        $this->assertSame(' JOIN `TableName` AS `Alias` ON Test', (string)$join);
    }

    /**
     * @covers app\decibel\database\statement\DJoin::__toString
     */
    public function test__toStringWithoutAlias()
    {
        $join = new DJoin('TableName', 'Test', null, 'Where');
        $this->assertInternalType('object', $join);
        $this->assertObjectHasAttribute('table', $join);
        $this->assertObjectHasAttribute('on', $join);
        $this->assertObjectHasAttribute('type', $join);
        $this->assertObjectHasAttribute('where', $join);
        $this->assertSame(' JOIN `TableName` ON Test', (string)$join);
    }

    /**
     * @covers app\decibel\database\statement\DJoin::getAlias
     */
    public function testgetAlias()
    {
        $join = new DJoin('table', 'on');
        $this->assertSame('table', $join->getAlias());
        $aliasedJoin = new DJoin('table', 'on', 'alias');
        $this->assertSame('alias', $aliasedJoin->getAlias());
    }

    /**
     * @covers app\decibel\database\statement\DJoin::getWhere
     */
    public function testwhere()
    {
        $join = new DJoin('TableName', 'Test', 'Alias', '1=1');
        $this->assertSame('1=1', $join->getWhere());
    }
}