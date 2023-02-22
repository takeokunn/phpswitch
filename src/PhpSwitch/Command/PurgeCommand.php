<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use Exception;
use PhpSwitch\BuildFinder;
use PhpSwitch\Config;

/**
 * @codeCoverageIgnore
 */
class PurgeCommand extends VirtualCommand
{
    public function arguments($args)
    {
        $args->add('PHP build')
            ->validValues(function () {
                return BuildFinder::findInstalledBuilds();
            })
            ->multiple()
            ;
    }

    public function brief()
    {
        return 'Remove installed php version and config files.';
    }
}
