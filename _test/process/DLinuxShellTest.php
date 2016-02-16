<?php
namespace tests\app\decibel\process;

use app\decibel\process\DLinuxShell;
use app\decibel\process\DShell;
use app\decibel\server\DServer;
use app\decibel\test\DTestCase;

/**
 * Test class for DLinuxShellTest.
 *
 * @requires OS Linux
 * @author David Stevens
 */
class DLinuxShellTest extends DTestCase
{
    /** @var DLinuxShell|DShell */
    private $shell;

    /**
     *
     */
    public function setUp()
    {
        $this->shell = DShell::create();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(DLinuxShell::class, $this->shell);
        $this->assertInstanceOf(DShell::class, $this->shell);
    }

    public function testSetEnvironmentVariable()
    {
        $shell = DLinuxShell::create('echo $DSHELL');
        $result = $shell->setEnvironmentVariable('DSHELL', 'Hello World');
        $this->assertInstanceOf(DLinuxShell::class, $result);
        $this->assertEquals('Hello World', $shell->run());
    }

    //
    //		/**
    //		 * @covers app\decibel\process\DLinuxShell::condition
    //		 */
    //		public function testConditionDirectoryFalse() {
    //			$this->assertSame(0, DLinuxShell::create()->condition('d', '/et'));
    //		}
    //
    //		/**
    //		 * @covers app\decibel\process\DLinuxShell::condition
    //		 */
    //		public function testConditionDirectoryTrue() {
    //			$this->assertSame(1, DLinuxShell::create()->condition('d', '/etc'));
    //		}
    //
    //		/**
    //		 * @covers app\decibel\process\DLinuxShell::condition
    //		 */
    //		public function testConditionFileFalse() {
    //			$this->assertSame(0, DLinuxShell::create()->condition('f', '/etc/failpower'));
    //		}
    //
    //		/**
    //		 * @covers app\decibel\process\DLinuxShell::condition
    //		 */
    //		public function testConditionFileTrue() {
    //			$this->assertSame(1, DLinuxShell::create()->condition('f', '/etc/passwd'));
    //		}
    //
    //		/**
    //		 * @covers app\decibel\process\DLinuxShell::condition
    //		 */
    //		public function testConditionSymbolicLinkTrue() {
    //
    //			DLinuxShell::create('touch /tmp/test-dshell.txt')->run();
    //			DLinuxShell::create('ln -s /tmp/test-dshell.txt /tmp/test-dshell')->run();
    //
    //			$this->assertSame(1, DLinuxShell::create()->condition('L', '/tmp/test-dshell'));
    //
    //			DLinuxShell::create('rm /tmp/test-dshell.txt')->run();
    //			DLinuxShell::create('unlink /tmp/test-dshell')->run();
    //		}

    /**
     * @covers app\decibel\process\DLinuxShell::isPipe
     */
    public function testIsPipe()
    {
        $this->assertSame(null, $this->shell->isPipe('/tmp'));
    }

    /**
     * @covers app\decibel\process\DLinuxShell::isSocket
     * @covers app\decibel\process\DLinuxShell::testFileSystemCondition
     */
    public function testIsSocket()
    {
        $this->assertFalse($this->shell->isSocket('/tmp'));
    }

    public function testRun()
    {
        $this->assertSame('root', DShell::create('sudo whoami')->run());
    }
}
