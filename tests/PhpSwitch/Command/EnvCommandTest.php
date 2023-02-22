<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Console;
use PhpSwitch\Testing\CommandTestCase;

/**
 * @group command
 */
class EnvCommandTest extends CommandTestCase
{
    public function setupApplication()
    {
        return new Console();
    }

    protected function setUp(): void
    {
        parent::setUp();
        putenv('PHPSWITCH_HOME=' . getcwd() . '/.phpswitch');
        putenv('PHPSWITCH_ROOT=' . getcwd() . '/.phpswitch');
    }

    /**
     * @outputBuffering enabled
     */
    public function testEnvCommand()
    {
        $this->assertCommandSuccess("phpswitch env");
    }
}
