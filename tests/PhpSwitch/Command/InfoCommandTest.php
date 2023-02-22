<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class InfoCommandTest extends CommandTestCase
{
    /**
     * @outputBuffering enabled
     */
    public function testInfoCommand()
    {
        $this->assertCommandSuccess("phpswitch info");
    }
}
