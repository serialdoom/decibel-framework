<?php
namespace tests\app\decibel\database;

use app\decibel\database\DQuery;
use app\decibel\test\DQueryTester;
use app\decibel\test\DTestCase;

/**
 * Test class for DQuery.
 * Generated by Decibel on 2011-10-31 at 14:08:29.
 */
class DQueryTest extends DTestCase
{
    /**
     * @covers {className}::{origMethodName}
     * @todo Implement test__destruct().
     */
    public function test__destruct()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\database\DQuery::__get
     * @expectedException app\decibel\debug\DInvalidPropertyException
     */
    public function test__get()
    {
        DQueryTester::create('DQuery::__get()');
        $query = new DQuery('DQuery::__get()');
        $query->test;
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGet().
     */
    public function testGet()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetInsertId().
     */
    public function testGetInsertId()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetNumRows().
     */
    public function testGetNumRows()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetNextRow().
     */
    public function testGetNextRow()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetResults().
     */
    public function testGetResults()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testGetError().
     */
    public function testGetError()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testEscapeValue().
     */
    public function testEscapeValue()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testSuccess().
     */
    public function testSuccess()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers {className}::{origMethodName}
     * @todo Implement testgenerateDebug().
     */
    public function testgenerateDebug()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers app\decibel\database\DQuery::getNextRow
     * @covers app\decibel\database\DQuery::getNumRows
     */
    public function testRegisterQueryTester()
    {
        DQueryTester::create(
            'SQL',
            array('column1', 'column2'),
            array(
                array(1, 2),
                array(3, 4),
            )
        );
        $query = new DQuery('SQL');
        $this->assertSame(2, $query->getNumRows());
        $this->assertSame(array('column1' => 1, 'column2' => 2), $query->getNextRow());
        $this->assertSame(array('column1' => 3, 'column2' => 4), $query->getNextRow());
        $this->assertNull($query->getNextRow());
    }
}