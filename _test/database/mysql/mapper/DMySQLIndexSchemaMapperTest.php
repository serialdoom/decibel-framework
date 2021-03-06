<?php
namespace tests\app\decibel\database\mysql\mapper;

use app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper;
use app\decibel\database\schema\DColumnDefinition;
use app\decibel\test\DTestCase;

if (!class_exists('tests\app\decibel\database\mysql\mapper\TestDColumnDefinition')) {
    class TestDColumnDefinition extends DColumnDefinition
    {
        public function testcompareNames($a, $b)
        {
            return static::compareNames($a, $b);
        }
    }
}

/**
 * Test class for DMySQLIndexSchemaMapper.
 * Generated by Decibel on 2011-10-31 at 14:07:42.
 */
class DMySQLIndexSchemaMapperTest extends DTestCase
{
    /**
     * @var DMySQLIndexSchemaMapper
     */
    protected $definition;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        // $this->definition = new DMySQLIndexSchemaMapper('testing');
    }

    /**
     * @covers app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper::getCreateSql
     */
    public function testgetCreateSql()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $index = new DIndexDefinition('testing', 'INDEX');
        $result = $index->getCreateSql();
        $this->assertInternalType('string', $result);
        $this->assertSame('KEY `testing` (``)', $result);
    }

    /**
     * @covers app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper::getCreateSql
     */
    public function testgetCreateSql_withTypeAndName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $index = new DIndexDefinition('testing', 'PRIMARY', array('test1', 'test2'));
        $result = $index->getCreateSql();
        $this->assertInternalType('string', $result);
        $this->assertSame('PRIMARY KEY (`test1`, `test2`)', $result);
    }

    /**
     * @covers app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper::getCreateSql
     */
    public function testgetCreateSql_indexWithTypeAndName()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $index = new DIndexDefinition('testing', 'INDEX', array('test1', 'test2'));
        $result = $index->getCreateSql();
        $this->assertInternalType('string', $result);
        $this->assertSame('KEY `testing` (`test1`, `test2`)', $result);
    }

    /**
     * @covers app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper::getModifySql
     */
    public function testgetModifySql()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $index = new DIndexDefinition('testing', 'PRIMARY', array('test1', 'test2'));
        $result = $index->getModifySql();
        $this->assertInternalType('string', $result);
        $this->assertSame('DROP PRIMARY KEY, ADD PRIMARY KEY (`test1`, `test2`)', $result);
    }

    /**
     * @covers app\decibel\database\mysql\mapper\DMySQLIndexSchemaMapper::getModifySql
     */
    public function testgetModifySql_custom()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $index = new DIndexDefinition('testing', 'mycolumn', array('test1', 'test2'));
        $result = $index->getModifySql();
        $this->assertInternalType('string', $result);
        $this->assertSame('DROP INDEX `testing`, ADD mycolumn `testing` (`test1`, `test2`)', $result);
    }
}
