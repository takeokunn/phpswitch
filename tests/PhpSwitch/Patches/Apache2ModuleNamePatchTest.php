<?php

namespace PhpSwitch\Tests\Patches;

use CLIFramework\Logger;
use PhpSwitch\Build;
use PhpSwitch\Patches\Apache2ModuleNamePatch;
use PhpSwitch\Testing\PatchTestCase;

/**
 * @small
 */
class Apache2ModuleNamePatchTest extends PatchTestCase
{
    public function versionProvider()
    {
        return array(
            array('5.5.17', 107, '/Makefile.global'),
            array('7.4.0', 25, '/build/Makefile.global')
        );
    }

    /**
     * @dataProvider versionProvider
     */
    public function testPatchVersion($version, $expectedPatchedCount, $makefile)
    {
        $logger = new Logger();
        $logger->setQuiet();

        $sourceDirectory = getenv('PHPSWITCH_BUILD_PHP_DIR');

        if (!is_dir($sourceDirectory)) {
            $this->markTestSkipped("$sourceDirectory does not exist.");
        }

        $this->setupBuildDirectory($version);

        $build = new Build($version);
        $build->setSourceDirectory($sourceDirectory);
        $build->enableVariant('apxs2');
        $this->assertTrue($build->isEnabledVariant('apxs2'));

        $patch = new Apache2ModuleNamePatch($version);
        $matched = $patch->match($build, $logger);
        $this->assertTrue($matched, 'patch matched');
        $patchedCount = $patch->apply($build, $logger);

        $expectedDirectory = getenv('PHPSWITCH_EXPECTED_PHP_DIR') . DIRECTORY_SEPARATOR . $version . '-apxs-patch';
        $this->assertEquals($expectedPatchedCount, $patchedCount);
        $this->assertFileEquals($expectedDirectory . $makefile, $sourceDirectory . $makefile);
        $this->assertFileEquals($expectedDirectory . '/configure', $sourceDirectory . '/configure');
    }
}
