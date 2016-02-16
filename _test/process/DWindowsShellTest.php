<?php
namespace tests\app\decibel\process;

use app\decibel\process\DShell;
use app\decibel\process\DWindowsShell;
use app\decibel\server\DServer;
use app\decibel\test\DTestCase;

/**
 * Test class for DShell.
 *
 * @requires OS WIN32|WINNT
 * @author David Stevens
 */
class DWindowsShellTest extends DShell
{
    public function setUp()
    {
        $this->shell = DShell::create();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(DWindowsShell::class, $this->shell);
        $this->assertInstanceOf(DShell::class, $this->shell);
    }

    public function testCondition()
    {
    }

    public function testIsDir()
    {
    }

    public function testIsFile()
    {
    }

    public function testIsLink()
    {
    }

    public function testIsPipe()
    {
    }

    public function testIsSocket()
    {
    }
}
