<?php

namespace PhpSwitch\Tests\Patches;

use CLIFramework\Logger;
use PhpSwitch\Build;
use PhpSwitch\Patches\OpenSSLDSOPatch;
use PhpSwitch\Testing\PatchTestCase;

/**
 * @small
 */
class OpenSSLDSOPatchTest extends PatchTestCase
{
    public function testPatch()
    {
        if (PHP_OS !== "Darwin") {
            $this->markTestSkipped('openssl DSO patch test only runs on darwin platform');
        }

        $logger = new Logger();
        $logger->setQuiet();

        $fromVersion = '5.5.17';
        $sourceDirectory = getenv('PHPSWITCH_BUILD_PHP_DIR');

        $this->setupBuildDirectory($fromVersion);

        $build = new Build($fromVersion);
        $build->setSourceDirectory($sourceDirectory);
        $build->enableVariant('openssl');
        $this->assertTrue($build->isEnabledVariant('openssl'));

        $openSSLDSOPatch = new OpenSSLDSOPatch();
        $matched = $openSSLDSOPatch->match($build, $logger);
        $this->assertTrue($matched, 'patch matched');
        $patchedCount = $openSSLDSOPatch->apply($build, $logger);
        $this->assertEquals(10, $patchedCount);
    }
}
