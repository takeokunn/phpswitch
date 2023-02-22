<?php

namespace PhpSwitch\Tests\Command;

use PhpSwitch\Testing\CommandTestCase;

/**
 * @large
 * @group command
 * @group noVCR
 */
class DownloadCommandTest extends CommandTestCase
{

    public function versionDataProvider()
    {
        return array(
            array('7.0'),
            array('7.0.33'),
        );
    }

    /**
     * @outputBuffering enabled
     * @dataProvider versionDataProvider
     */
    public function testDownloadCommand($versionName)
    {
        if (getenv('GITHUB_ACTIONS')) {
            $this->markTestSkipped('Skip heavy test on Travis');
        }

        $this->assertCommandSuccess("phpswitch init");
        $this->assertCommandSuccess("phpswitch -q download $versionName");

        // re-download should just check the checksum instead of extracting it
        $this->assertCommandSuccess("phpswitch -q download $versionName");
        $this->assertCommandSuccess("phpswitch -q download -f $versionName");
    }
}
