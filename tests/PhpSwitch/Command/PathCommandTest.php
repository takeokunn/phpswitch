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
        return array(
            array("build",   "#\.phpswitch/build/.+#"),
            array("ext-src", "#\.phpswitch/build/.+/ext$#"),
            array("include", "#\.phpswitch/php/.+/include$#"),
            array("etc",     "#\.phpswitch/php/.+/etc$#"),
            array("dist",    "#\.phpswitch/distfiles$#"),
            array("root",    "#\.phpswitch$#"),
            array("home",    "#\.phpswitch$#"),
        );
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
