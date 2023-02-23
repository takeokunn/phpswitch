<?php

namespace PhpSwitch\Tests\Extension;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Extension\ExtensionDownloader;
use PhpSwitch\Extension\ExtensionFactory;
use PhpSwitch\Extension\ExtensionManager;
use PhpSwitch\Extension\Provider\PeclProvider;
use PhpSwitch\Testing\CommandTestCase;

/**
 * NOTE: This depends on an existing installed php build. we need to ensure
 * that the installer test runs before this test.
 *
 * @large
 * @group extension
 */
class ExtensionInstallerTest extends CommandTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $versionName = $this->getPrimaryVersion();
        $this->runCommand("phpswitch use php-{$versionName}");
    }

    /**
     * @group noVCR
     */
    public function testPackageUrl()
    {
        if (getenv('GITHUB_ACTIONS')) {
            $this->markTestSkipped('Skipping since VCR cannot properly record this request');
        }

        $logger = new Logger();
        $logger->setQuiet();
        $peclProvider = new PeclProvider($logger, new OptionResult());
        $extensionDownloader = new ExtensionDownloader($logger, new OptionResult());
        $peclProvider->setPackageName('APCu');
        $extractPath = $extensionDownloader->download($peclProvider, 'latest');
        $this->assertFileExists($extractPath);
    }

    public function packageNameProvider()
    {
        return [
            // xdebug requires at least php 5.4
            // array('xdebug'),
            [version_compare(PHP_VERSION, '5.5', '=='), 'APCu', 'stable', []],
        ];
    }

    /**
     * @dataProvider packageNameProvider
     */
    public function testInstallPackages($build, $extensionName, $extensionVersion, $options)
    {
        if (!$build) {
            $this->markTestSkipped('skip extension build test');
            return;
        }
        $logger = new Logger();
        $logger->setDebug();
        $extensionManager = new ExtensionManager($logger);
        $peclProvider = new PeclProvider();
        $extensionDownloader = new ExtensionDownloader($logger, new OptionResult());
        $peclProvider->setPackageName($extensionName);
        $extensionDownloader->download($peclProvider, $extensionVersion);
        $ext = ExtensionFactory::lookup($extensionName);
        $this->assertNotNull($ext);
        $extensionManager->installExtension($ext, $options);
    }
}
