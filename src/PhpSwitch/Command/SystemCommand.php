<?php

namespace PhpSwitch\Command;

use CLIFramework\Command;
use PhpSwitch\BuildFinder;

class SystemCommand extends Command
{
    public function brief()
    {
        return 'Get or set the internally used PHP binary';
    }

    public function arguments($args)
    {
        $args->add('php version')
            ->suggestions(fn() => BuildFinder::findInstalledBuilds());
    }

    final public function execute()
    {
        $path = getenv('PHPSWITCH_SYSTEM_PHP');

        if ($path !== false && $path !== '') {
            $this->logger->writeln($path);
        }
    }
}
