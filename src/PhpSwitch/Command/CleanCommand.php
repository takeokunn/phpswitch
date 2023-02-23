<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use PhpSwitch\Build;
use PhpSwitch\BuildFinder;
use PhpSwitch\Config;
use PhpSwitch\Tasks\MakeTask;
use PhpSwitch\Utils;

class CleanCommand extends Command
{
    public function brief()
    {
        return 'Clean up the source directory of a PHP distribution';
    }

    public function usage()
    {
        return 'phpswitch clean [-a|--all] [php-version]';
    }

    public function options($opts)
    {
        $opts->add('a|all', 'Remove all the files in the source directory of the PHP distribution.');
    }

    public function arguments($args)
    {
        $args->add('PHP build')
            ->validValues(fn() => BuildFinder::findInstalledBuilds())
            ;
    }

    public function execute($version)
    {
        $buildDir = Config::getBuildDir() . DIRECTORY_SEPARATOR . $version;
        if ($this->options->all) {
            if (!file_exists($buildDir)) {
                $this->logger->info('Source directory ' . $buildDir . ' does not exist.');
            } else {
                $this->logger->info('Source directory ' . $buildDir . ' found, deleting...');
                Utils::recursive_unlink($buildDir, $this->logger);
            }
        } else {
            $makeTask = new MakeTask($this->logger);
            $makeTask->setQuiet();
            $build = new Build($version);
            $build->setSourceDirectory($buildDir);
            if ($makeTask->clean($build)) {
                $this->logger->info('Distribution is cleaned up. Woof! ');
            }
        }
    }
}
