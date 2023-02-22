<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class UpdateCommandTest extends CommandTestCase
{
    public $usesVCR = true;

    /**
     * @outputBuffering enabled
     * @group mayignore
     */
    public function testUpdateCommand()
    {
        $this->assertCommandSuccess("phpswitch --quiet update --old");
    }
}
