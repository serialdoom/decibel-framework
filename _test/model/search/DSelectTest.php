<?php
namespace tests\app\decibel\model\search;

use app\decibel\model\search\DBaseModelSearch;
use app\decibel\model\search\DSelect;
use app\decibel\test\DTestCase;

class TestSelect extends DSelect
{
    public function __construct($alias = null, $returnType = null, $aggregateFunction = null)
    {
        parent::__construct();
        if ($alias !== null) {
            $this->alias = $alias;
        }
        if ($returnType !== null) {
            $this->returnType = $returnType;
        }
        if ($aggregateFunction !== null) {
            $this->aggregateFunction = $aggregateFunction;
        }
    }

    public function getFieldName()
    {
        return 'fieldName';
    }

    public function getSelect(DBaseModelSearch $search, $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED)
    {
    }

    public function processRow(DBaseModelSearch $search, &$row,
                               $defaultReturnType = DBaseModelSearch::RETURN_SERIALIZED)
    {
    }
}

/**
 * Test class for DSelect.
 * Generated by Decibel on 2012-04-12 at 09:08:49.
 */
class DSelectTest extends DTestCase
{
    /**
     * @covers app\decibel\model\search\DSelect::__construct
     * @covers app\decibel\model\search\DSelect::getAggregateFunction
     */
    public function test__construct()
    {
        $select = new TestSelect();
        $this->assertInstanceOf('app\\decibel\\model\\search\\DSelect', $select);
        $this->assertSame(DBaseModelSearch::AGGREGATE_NONE, $select->getAggregateFunction());
    }

    /**
     * @covers app\decibel\model\search\DSelect::getAlias
     */
    public function testgetAlias()
    {
        $select = new TestSelect();
        $this->assertNull($select->getAlias());
        $aliasSelect = new TestSelect('alias');
        $this->assertSame('alias', $aliasSelect->getAlias());
    }

    /**
     * @covers app\decibel\model\search\DSelect::getCacheId
     */
    public function testgetCacheId()
    {
        $select = new TestSelect();
        $this->assertSame('fieldName', $select->getCacheId());
        $returnTypeSelect = new TestSelect(null, DBaseModelSearch::RETURN_SERIALIZED);
        $this->assertSame('fieldName|serialized', $returnTypeSelect->getCacheId());
        $aggregateSelect = new TestSelect(null, null, DBaseModelSearch::AGGREGATE_AVG);
        $this->assertSame('fieldName|AVG', $aggregateSelect->getCacheId());
        $bothSelect = new TestSelect(null, DBaseModelSearch::RETURN_SERIALIZED, DBaseModelSearch::AGGREGATE_AVG);
        $this->assertSame('fieldName|serialized|AVG', $bothSelect->getCacheId());
    }

    /**
     * @covers app\decibel\model\search\DSelect::getReturnType
     */
    public function testgetReturnType()
    {
        $select = new TestSelect();
        $this->assertNull($select->getReturnType());
        $returnTypeSelect = new TestSelect(null, DBaseModelSearch::RETURN_SERIALIZED);
        $this->assertSame(DBaseModelSearch::RETURN_SERIALIZED, $returnTypeSelect->getReturnType());
    }
}
