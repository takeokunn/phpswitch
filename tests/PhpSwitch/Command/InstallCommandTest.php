<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\BuildFinder;
use PhpSwitch\Testing\CommandTestCase;

/**
 * The install command tests are heavy.
 *
 * Don't catch the exceptions, the system command exception
 * will show up the error message.
 *
 * Build output will be shown when assertion failed.
 *
 * @large
 * @group command
 * @group noVCR
 */
class InstallCommandTest extends CommandTestCase
{
    public $usesVCR = false;

    /**
     * @group install
     * @group mayignore
     */
    public function testInstallCommand()
    {
        if (getenv('GITHUB_ACTIONS')) {
            $this->markTestSkipped('Skip heavy test on Travis');
        }

        $this->assertCommandSuccess("phpswitch init");
        $this->assertCommandSuccess("phpswitch known --update");

        $versionName = $this->getPrimaryVersion();
        $this->assertCommandSuccess("phpswitch install php-{$versionName} +cli+posix+intl+gd");
        $this->assertListContains("php-{$versionName}");
    }

    /**
     * @depends testInstallCommand
     */
    public function testEnvCommand()
    {
        $versionName = $this->getPrimaryVersion();
        $this->assertCommandSuccess("phpswitch env php-{$versionName}");
    }

    /**
     * @depends testInstallCommand
     * @group mayignore
     */
    public function testCtagsCommand()
    {
        $versionName = $this->getPrimaryVersion();
        $this->assertCommandSuccess("phpswitch ctags php-{$versionName}");
    }

    /**
     * @group install
     * @group mayignore
     */
    public function testGitHubInstallCommand()
    {
        if (getenv('GITHUB_ACTIONS')) {
            $this->markTestSkipped('Skip heavy test on Travis');
        }

        $this->assertCommandSuccess(
            'phpswitch --debug install --dryrun github:php/php-src@PHP-7.0 as php-7.0.0 +cli+posix'
        );
    }

    /**
     * @depends testInstallCommand
     * @group install
     */
    public function testInstallAsCommand()
    {
        $versionName = $this->getPrimaryVersion();
        $this->assertCommandSuccess("phpswitch install php-{$versionName} as myphp +cli+soap");
        $this->assertListContains("myphp");
    }

    /**
     * @depends testInstallCommand
     */
    public function testCleanCommand()
    {
        $versionName = $this->getPrimaryVersion();
        $this->assertCommandSuccess("phpswitch clean php-{$versionName}");
    }

    protected function assertListContains($string)
    {
        $this->assertContains($string, BuildFinder::findInstalledBuilds());
    }
}
