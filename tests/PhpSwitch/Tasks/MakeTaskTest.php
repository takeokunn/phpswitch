<?php

namespace PhpSwitch\Tests\Tasks;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Tasks\MakeTask;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class MakeTaskTest extends TestCase
{
    private \PhpSwitch\Tasks\MakeTask $makeTask;

    public function createLogger()
    {
        $logger = new Logger();
        $logger->setQuiet();
        return $logger;
    }

    protected function setUp(): void
    {
        $this->makeTask = new MakeTask($this->createLogger(), new OptionResult());
        $this->makeTask->setQuiet();
    }

    public function testMakeInstall()
    {
        $this->assertTrue($this->makeTask->install(new MakeTaskTestBuild()));
    }

    public function testMakeClean()
    {
        $this->assertTrue($this->makeTask->clean(new MakeTaskTestBuild()));
    }

    public function testRunWithValidTarget()
    {
        $makeTaskTestBuild = new MakeTaskTestBuild();
        $this->assertTrue($this->makeTask->run($makeTaskTestBuild));
    }

    public function testWhenThereIsNoMakefile()
    {
        // ignores error messages generated by make command
        ob_start();
        $this->assertFalse($this->makeTask->install(new MakeTaskTestNoSuchFileBuild()));
        ob_end_clean();
    }

    public function testSetQuiet()
    {
        $makeTask = new MakeTask($this->createLogger(), new OptionResult());

        $this->assertFalse($makeTask->isQuiet());

        $makeTask->setQuiet();

        $this->assertTrue($makeTask->isQuiet());
    }
}
