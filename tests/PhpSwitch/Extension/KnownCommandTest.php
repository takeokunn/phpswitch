<?php

namespace PhpSwitch\Tests\Extension;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Extension\ExtensionDownloader;
use PhpSwitch\Extension\Provider\BitbucketProvider;
use PhpSwitch\Extension\Provider\GithubProvider;
use PhpSwitch\Extension\Provider\PeclProvider;
use PhpSwitch\Testing\CommandTestCase;

class KnownCommandTest extends CommandTestCase
{
    public $usesVCR = true;

    public function testPeclPackage()
    {

        $logger = new Logger();
        $logger->setQuiet();

        $peclProvider = new PeclProvider($logger, new OptionResult());
        $peclProvider->setPackageName('xdebug');

        $extensionDownloader = new ExtensionDownloader($logger, new OptionResult());
        $versionList = $extensionDownloader->knownReleases($peclProvider);

        $this->assertNotCount(0, $versionList);
    }

    public function testGithubPackage()
    {
        if (getenv('GITHUB_ACTIONS')) {
            $this->markTestSkipped('Avoid bugging GitHub on Travis since the test is likely to fail because of a 403');
        }

        $logger = new Logger();
        $logger->setQuiet();

        $githubProvider = new GithubProvider();
        $githubProvider->setOwner('phalcon');
        $githubProvider->setRepository('cphalcon');
        $githubProvider->setPackageName('phalcon');
        if (getenv('github_token')) { //load token from travis-ci
            $githubProvider->setAuth(getenv('github_token'));
        }

        $extensionDownloader = new ExtensionDownloader($logger, new OptionResult());
        $versionList = $extensionDownloader->knownReleases($githubProvider);
        $this->assertNotCount(0, $versionList);
    }

    public function testBitbucketPackage()
    {

        $logger = new Logger();
        $logger->setQuiet();

        $bitbucketProvider = new BitbucketProvider();
        $bitbucketProvider->setOwner('osmanov');
        $bitbucketProvider->setRepository('pecl-event');
        $bitbucketProvider->setPackageName('event');

        $extensionDownloader = new ExtensionDownloader($logger, new OptionResult());
        $versionList = $extensionDownloader->knownReleases($bitbucketProvider);

        $this->assertNotCount(0, $versionList);
    }
}
