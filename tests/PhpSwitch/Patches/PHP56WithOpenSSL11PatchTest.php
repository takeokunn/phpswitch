<?php

namespace PhpSwitch\Tests\Patches;

use CLIFramework\Logger;
use PhpSwitch\Build;
use PhpSwitch\Patches\PHP56WithOpenSSL11Patch;
use PhpSwitch\Testing\PatchTestCase;

class PHP56WithOpenSSL11PatchTest extends PatchTestCase
{
    /**
     * @dataProvider versionProvider
     */
    public function testPatchVersion($version)
    {
        $logger = new Logger();
        $logger->setQuiet();

        $sourceDirectory = getenv('PHPSWITCH_BUILD_PHP_DIR');

        $this->setupBuildDirectory($version);

        $build = new Build($version);
        $build->setSourceDirectory($sourceDirectory);
        $build->enableVariant('openssl');
        $this->assertTrue($build->isEnabledVariant('openssl'));

        $php56WithOpenSSL11Patch = new PHP56WithOpenSSL11Patch();
        $this->assertTrue($php56WithOpenSSL11Patch->match($build, $logger));

        $this->assertGreaterThan(0, $php56WithOpenSSL11Patch->apply($build, $logger));

        $expectedDirectory = getenv('PHPSWITCH_EXPECTED_PHP_DIR') . '/' . $version . '-php56-openssl11-patch';

        foreach (
            ['ext/openssl/openssl.c', 'ext/openssl/xp_ssl.c', 'ext/phar/util.c'] as $path
        ) {
            $this->assertFileEquals(
                $expectedDirectory . '/' .  $path,
                $sourceDirectory . '/' . $path
            );
        }
    }

    public static function versionProvider()
    {
        return [['5.6.40']];
    }
}
