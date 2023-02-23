<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Build;
use PhpSwitch\CommandBuilder;
use PhpSwitch\Exception\SystemCommandException;

/**
 * Task to run `make test`.
 */
class TestTask extends BaseTask
{
    public function run(Build $build, $nice = null)
    {
        $this->info('===> Running tests...');
        $commandBuilder = new CommandBuilder('make test');

        if ($nice) {
            $commandBuilder->nice($nice);
        }

        $commandBuilder->setAppendLog(true);
        $commandBuilder->setLogPath($build->getBuildLogPath());
        $commandBuilder->setStdout($this->options->{'stdout'});

        putenv('NO_INTERACTION=1');
        $this->debug('' . $commandBuilder);
        $code = $commandBuilder->execute($lastline);
        if ($code !== 0) {
            throw new SystemCommandException("Test failed: $lastline", $build);
        }
    }
}
