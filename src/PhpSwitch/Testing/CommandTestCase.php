<?php

namespace PhpSwitch\Testing;

use CLIFramework\Testing\CommandTestCase as BaseCommandTestCase;
use GetOptionKit\Option;
use PhpSwitch\Console;

abstract class CommandTestCase extends BaseCommandTestCase
{
    protected $debug = false;

    private $previousPhpSwitchRoot;

    private $previousPhpSwitchHome;

    public $primaryVersion = '7.0.33';

    /**
     * You need to set this to true in each subclass you want to use VCR in.
     */
    public $usesVCR = false;

    public function getPrimaryVersion()
    {
        /*
        if ($version = getenv('TRAVIS_PHP_VERSION')) {
            return "php-$version";
        }
        */
        return $this->primaryVersion;
    }

    public function setupApplication()
    {
        $console = Console::getInstance();
        $console->getLogger()->setQuiet();
        $console->getFormatter()->preferRawOutput();

        return $console;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousPhpSwitchRoot = getenv('PHPSWITCH_ROOT');
        $this->previousPhpSwitchHome = getenv('PHPSWITCH_HOME');

        // <env name="PHPSWITCH_ROOT" value=".phpswitch"/>
        // <env name="PHPSWITCH_HOME" value=".phpswitch"/>

        // already setup in phpunit.xml, but it seems don't work.
        // putenv('PHPSWITCH_ROOT=' . getcwd() . '/.phpswitch');
        // putenv('PHPSWITCH_HOME=' . getcwd() . '/.phpswitch');

        if ($options = Console::getInstance()->options) {
            $option = new Option('no-progress');
            $option->setValue(true);
            $options->set('no-progress', $option);
        }

        if ($this->usesVCR) {
            VCRAdapter::enableVCR($this);
        }
    }

    /*
     * we don't have to restore it back. the parent environment variables
     * won't change if the they are changed inside a process.
     * but we might want to change it back if there is a test changed the environment variable.
     */
    protected function tearDown(): void
    {
        if ($this->previousPhpSwitchRoot !== null) {
            // putenv('PHPSWITCH_ROOT=' . $this->previousPhpswitchRoot);
        }
        if ($this->previousPhpSwitchHome !== null) {
            // putenv('PHPSWITCH_HOME=' . $this->previousPhpswitchHome);
        }

        if ($this->usesVCR) {
            VCRAdapter::disableVCR();
        }
    }

    public function assertCommandSuccess($args)
    {
        try {
            if ($this->debug) {
                fwrite(STDERR, $args . PHP_EOL);
            }

            ob_start();
            $ret = parent::runCommand($args);
            $output = ob_get_contents();
            ob_end_clean();

            $this->assertTrue($ret, $output);
        } catch (\CurlKit\CurlException $e) {
            $this->markTestIncomplete($e->getMessage());
        }
    }

    public function runCommand($args)
    {
        ob_start();
        $status = parent::runCommand($args);
        ob_end_clean();

        return $status;
    }

    public function runCommandWithStdout($args)
    {
        return parent::runCommand($args);
    }
}
