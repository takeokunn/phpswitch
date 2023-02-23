<?php

namespace PhpSwitch\Tasks;

use PhpSwitch\Build;
use PhpSwitch\CommandBuilder;
use PhpSwitch\Exception\SystemCommandException;

/**
 * Task to run `make`.
 */
class BuildTask extends BaseTask
{
    public function run(Build $build, $targets = [])
    {
        if ($build->getState() >= Build::STATE_BUILD) {
            $this->info('===> Already built, skipping...');

            return;
        }

        $this->info('===> Building...');
        $commandBuilder = new CommandBuilder('make');

        $commandBuilder->setAppendLog(true);
        $commandBuilder->setLogPath($build->getBuildLogPath());
        $commandBuilder->setStdout($this->options->{'stdout'});

        if (!empty($targets)) {
            foreach ($targets as $target) {
                $commandBuilder->addArg($target);
            }
        }

        if ($this->options->nice) {
            $commandBuilder->nice($this->options->nice);
        }

        if ($makeJobs = $this->options->{'jobs'}) {
            $commandBuilder->addArg("-j{$makeJobs}");
        }

        $this->debug($commandBuilder->buildCommand());

        if (!$this->options->dryrun) {
            $startTime = microtime(true);
            $code = $commandBuilder->execute($lastline);
            if ($code !== 0) {
                throw new SystemCommandException("Make failed: $lastline", $build, $build->getBuildLogPath());
            }
            $buildTime = round((microtime(true) - $startTime) / 60, 1);
            $this->info("Build finished: $buildTime minutes.");
        }
        $build->setState(Build::STATE_BUILD);
    }
}
