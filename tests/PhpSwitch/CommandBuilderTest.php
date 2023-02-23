<?php

namespace PhpSwitch\Tests;

use PhpSwitch\CommandBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class CommandBuilderTest extends TestCase
{
    public function test()
    {
        ob_start();
        $commandBuilder = new CommandBuilder('ls');
        $this->assertEquals(0, $commandBuilder->execute());
        ob_end_clean();
    }

    /**
     * @dataProvider provideTestGetCommandTestCases
     */
    public function testGetCommand($appendLog, $stdout, $logPath, $expected)
    {
        $commandBuilder = new CommandBuilder('ls');
        $commandBuilder->setAppendLog($appendLog);
        $commandBuilder->setStdout($stdout);
        $commandBuilder->setLogPath($logPath);
        $this->assertEquals($expected, $commandBuilder->buildCommand());
        ob_start();
        $this->assertEquals(0, $commandBuilder->execute());
        ob_end_clean();
    }

    public function provideTestGetCommandTestCases()
    {
        return [['appendLog' => true, 'stdout'    => true, 'logPath'   => '/tmp/build.log', 'expected'  => 'ls 2>&1'], ['appendLog' => false, 'stdout'    => true, 'logPath'   => '/tmp/build.log', 'expected'  => 'ls 2>&1'], ['appendLog' => true, 'stdout'    => false, 'logPath'   => '/tmp/build.log', 'expected'  => "ls >> '/tmp/build.log' 2>&1"], ['appendLog' => false, 'stdout'    => false, 'logPath'   => '/tmp/build with whitespaces.log', 'expected'  => "ls > '/tmp/build with whitespaces.log' 2>&1"], ['appendLog' => true, 'stdout'    => false, 'logPath'   => null, 'expected'  => 'ls'], ['appendLog' => false, 'stdout'    => false, 'logPath'   => null, 'expected'  => 'ls'], ['appendLog' => true, 'stdout'    => false, 'logPath'   => null, 'expected'  => 'ls'], ['appendLog' => false, 'stdout'    => false, 'logPath'   => null, 'expected'  => 'ls']];
    }
}
