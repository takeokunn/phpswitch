<?php

namespace PhpSwitch\Tests\Patches;

use CLIFramework\Logger;
use PhpSwitch\Build;
use PhpSwitch\Testing\PatchTestCase;

class FreeTypePatchTest extends \PhpSwitch\Testing\PatchTestCase
{
    public function testPatch()
    {
        $logger = new \CLIFramework\Logger();
        $logger->setQuiet();

        $sourceDirectory = \getenv('PHPSWITCH_BUILD_PHP_DIR');

        $this->setupBuildDirectory('7.3.12');

        $build = new \PhpSwitch\Build('7.3.12');
        $build->setSourceDirectory($sourceDirectory);
        $build->enableVariant('gd');

        $freeTypePatch = new \PhpSwitch\Patches\FreeTypePatch();
        $this->assertTrue($freeTypePatch->match($build, $logger));
        $this->assertGreaterThan(0, $freeTypePatch->apply($build, $logger));

        $expectedDirectory = \getenv('PHPSWITCH_EXPECTED_PHP_DIR') . DIRECTORY_SEPARATOR . '7.3.12-freetype-patch';
        $this->assertFileEquals($expectedDirectory . '/ext/gd/config.m4', $sourceDirectory . '/ext/gd/config.m4');
    }
}
