<?php
namespace tests\app\decibel\process;

use app\decibel\process\DShell;
use app\decibel\server\DServer;
use app\decibel\test\DTestCase;

/**
 * Test class for DShell.
 *
 * @author        David Stevens
 */
class DShellTest extends DTestCase
{
    public function testCreate()
    {
        $shell = DShell::create();
        // Test the object type depending on the system.
        if (DServer::isWindows()) {
            $this->assertInstanceOf('app\decibel\process\DWindowsShell', $shell);
            $this->assertInstanceOf('app\decibel\process\DShell', $shell);
        } else {
            $this->assertInstanceOf('app\decibel\process\DLinuxShell', $shell);
            $this->assertInstanceOf('app\decibel\process\DShell', $shell);
        }
    }

    public function testSetInput()
    {
        $object = DShell::create()->setInput('sudo whoami');
        $this->assertInstanceOf('app\decibel\process\DShell', $object);
        //		$this->assertSame('root', $object->getCommand());
    }

    public function testSetTimeout()
    {
        $object = DShell::create()->setTimeout(10);
        $this->assertInstanceOf('app\decibel\process\DShell', $object);
        $this->assertSame(10000000, $object->getTimeout());
    }
}
