<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class ListCommandTest extends CommandTestCase
{
    /**
     * @outputBuffering enabled
     */
    public function testListCommand()
    {
        $this->assertCommandSuccess("phpswitch list");
    }
}
