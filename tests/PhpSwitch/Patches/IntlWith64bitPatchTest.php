<?php

namespace PhpSwitch\Tests\Patches;

use CLIFramework\Logger;
use PhpSwitch\Build;
use PhpSwitch\Patches\IntlWith64bitPatch;
use PhpSwitch\Testing\PatchTestCase;

class IntlWith64bitPatchTest extends PatchTestCase
{
    public function testPatch()
    {
        $logger = new Logger();
        $logger->setQuiet();

        $fromVersion = '5.3.29';
        $sourceFixtureDirectory = getenv('PHPSWITCH_FIXTURES_PHP_DIR') . DIRECTORY_SEPARATOR . $fromVersion;
        $sourceDirectory = getenv('PHPSWITCH_BUILD_PHP_DIR');

        if (!is_dir($sourceDirectory)) {
            $this->markTestSkipped("$sourceDirectory does not exist.");
        }

        // Copy the source Makefile to the Makefile
        // copy($sourceFixtureDirectory . '/Makefile', $sourceDirectory . '/Makefile');
        $this->setupBuildDirectory($fromVersion);

        $build = new Build($fromVersion);
        $build->setSourceDirectory($sourceDirectory);
        $build->enableVariant('intl');
        $this->assertTrue($build->isEnabledVariant('intl'));

        $patch = new IntlWith64bitPatch();
        $matched = $patch->match($build, $logger);
        $this->assertTrue($matched, 'patch matched');
        $patchedCount = $patch->apply($build, $logger);
        $this->assertEquals(3, $patchedCount);

        $sourceExpectedDirectory = getenv('PHPSWITCH_EXPECTED_PHP_DIR') . DIRECTORY_SEPARATOR . $fromVersion;
        $this->assertFileEquals($sourceExpectedDirectory . '/Makefile', $sourceDirectory . '/Makefile');
    }
}
