<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class VariantsCommandTest extends CommandTestCase
{
    /**
     * @outputBuffering enabled
     */
    public function testVariantsCommand()
    {
        $this->assertCommandSuccess("phpswitch variants");
    }
}
