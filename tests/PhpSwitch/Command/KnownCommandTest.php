<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class KnownCommandTest extends CommandTestCase
{
    public $usesVCR = true;

    /**
     * @outputBuffering enabled
     * @group mayignore
     */
    public function testCommand()
    {
        $this->assertCommandSuccess('phpswitch --quiet known');
    }

    /**
     * @outputBuffering enabled
     * @group mayignore
     */
    public function testMoreOption()
    {
        $this->assertCommandSuccess('phpswitch --quiet known --more');
    }

    /**
     * @outputBuffering enabled
     * @group mayignore
     */
    public function testOldMoreOption()
    {
        $this->assertCommandSuccess('phpswitch --quiet known --old --more');
    }


    /**
     * @outputBuffering enabled
     * @group mayignore
     */
    public function testKnownUpdateCommand()
    {
        $this->assertCommandSuccess('phpswitch --quiet known --update');
        $this->assertCommandSuccess('phpswitch --quiet known -u');
    }
}
