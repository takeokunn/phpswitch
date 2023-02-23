<?php

namespace PhpSwitch\Command;

/*
 * @codeCoverageIgnore
 */
use CLIFramework\Command;
use CLIFramework\Prompter;
use Exception;
use PhpSwitch\BuildFinder;
use PhpSwitch\Config;
use PhpSwitch\Utils;

class RemoveCommand extends Command
{
    public function brief()
    {
        return 'Remove installed php build.';
    }

    public function arguments($args)
    {
        $args->add('installed php')
            ->validValues(fn() => BuildFinder::findInstalledBuilds())
            ;
    }

    public function execute($buildName)
    {
        $prefix = Config::getVersionInstallPrefix($buildName);
        if (!file_exists($prefix)) {
            throw new Exception("$prefix does not exist.");
        }
        $prompter = new Prompter();
        $answer = $prompter->ask("Are you sure to delete $buildName?", ['Y', 'n'], 'Y');
        if (strtolower((string) $answer) == 'y') {
            Utils::recursive_unlink($prefix, $this->logger);
            $this->logger->info("$buildName is removed.  I hope you're not surprised. :)");
        } else {
            $this->logger->info('Let me guess, you drunk tonight.');
        }
    }
}
