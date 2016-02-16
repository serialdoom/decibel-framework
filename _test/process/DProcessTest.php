<?php
namespace tests\app\decibel\process;

use app\decibel\process\DProcess;
use app\decibel\server\DServer;
use app\decibel\test\DTestCase;

/**
 * Test class for DProcess.
 *
 * @author        David Stevens
 */
class DProcessTest extends DTestCase
{
    /**
     * @covers app\decibel\process\DProcess::getCommand
     */
    public function testGetCommandStringPid()
    {
        if (DServer::isWindows()) {
            $testPid = 0;
        } else {
            $testPid = 1;
        }
        $process = DProcess::find($testPid);
        $this->assertInstanceOf('app\\decibel\\process\\DProcess', $process);
        $this->assertSame($testPid, $process->getPid());
        $this->assertNotEmpty($process->getCommand());
    }

    /**
     * @covers app\decibel\process\DProcess::__construct
     * @covers app\decibel\process\DProcess::find
     * @covers app\decibel\process\DProcess::getPid
     * @covers app\decibel\process\DProcess::getCommand
     */
    public function testfind()
    {
        $currentPid = getmypid();
        $process = DProcess::find($currentPid);
        $this->assertInstanceOf('app\\decibel\\process\\DProcess', $process);
        $this->assertSame($currentPid, $process->getPid());
    }

    /**
     * @covers app\decibel\process\DProcess::__construct
     * @covers app\decibel\process\DProcess::find
     * @expectedException app\decibel\process\DInvalidProcessIdException
     */
    public function testfind_invalid()
    {
        $this->assertNull(DProcess::find(9999999999));
    }

    /**
     * @covers app\decibel\process\DProcess::kill
     */
    public function testkill_pid_force()
    {
        if (DServer::isWindows()) {
            // TODO
        } else {
            // command to kill
            $command = 'sleep 1000000';
            // execute command.
            `$command > /dev/null 2>&1 &`;
            // find pid number of command to kill.
            $pid = (int)`ps -eaf | grep -v grep | grep '$command' | awk '{print $2}'`;
            $process = DProcess::find($pid);
            $this->assertFalse($process->kill());
        }
    }

    /**
     * @covers app\decibel\process\DProcess::kill
     */
    public function testkill_pid()
    {
        if (DServer::isWindows()) {
            // TODO
        } else {
            // command to kill
            $command = 'sleep 1000000';
            // command to execute.
            `$command > /dev/null 2>&1 &`;
            // command to find pid number from.
            $pid = (int)`ps -eaf | grep -v grep | grep '$command' | awk '{print $2}'`;
            $process = DProcess::find($pid);
            $this->assertFalse($process->kill(false));
        }
    }
}
