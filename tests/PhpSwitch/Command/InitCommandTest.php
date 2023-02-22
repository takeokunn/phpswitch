<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class InitCommandTest extends CommandTestCase
{
    /**
     * @outputBuffering enabled
     */
    public function testInitCommand()
    {
        ob_start();
        $this->assertTrue($this->runCommand("phpswitch init"));
        ob_end_clean();
    }
}
