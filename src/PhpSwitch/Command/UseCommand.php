<?php

namespace PhpSwitch\Command;

use PhpSwitch\BuildFinder;

/**
 * @codeCoverageIgnore
 */
class UseCommand extends VirtualCommand
{
    public function arguments($args)
    {
        $args->add('PHP version')
            ->validValues(fn() => BuildFinder::findInstalledVersions())
            ;
    }

    public function brief()
    {
        return 'Use php, switch version temporarily';
    }
}
