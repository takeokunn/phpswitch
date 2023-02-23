<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 */
class PathCommandTest extends CommandTestCase
{
    public function argumentsProvider()
    {
        return [["build", "#\.phpswitch/build/.+#"], ["ext-src", "#\.phpswitch/build/.+/ext$#"], ["include", "#\.phpswitch/php/.+/include$#"], ["etc", "#\.phpswitch/php/.+/etc$#"], ["dist", "#\.phpswitch/distfiles$#"], ["root", "#\.phpswitch$#"], ["home", "#\.phpswitch$#"]];
    }

    /**
     * @outputBuffering enabled
     * @dataProvider argumentsProvider
     */
    public function testPathCommand($arg, $pattern)
    {
        putenv('PHPSWITCH_PHP=7.4.0');

        ob_start();
        $this->runCommandWithStdout("phpswitch path $arg");
        $path = ob_get_clean();
        $this->assertRegExp($pattern, $path);
    }
}
