<?php

namespace PhpSwitch\Command;

use PhpSwitch\BuildFinder;

/**
 * @codeCoverageIgnore
 */
class SwitchCommand extends VirtualCommand
{
    public function arguments($args)
    {
        $args->add('PHP version')
            ->validValues(function () {
                return BuildFinder::findInstalledVersions();
            })
            ;
    }

    public function brief()
    {
        return 'Switch default php version.';
    }
}
